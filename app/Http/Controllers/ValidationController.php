<?php

namespace App\Http\Controllers;

use App\Models\Activite;
use App\Models\Departement;
use App\Models\Extrant;
use App\Notifications\ActiviteArbitrageNotification;
use App\Notifications\ActiviteRefusee;
use App\Notifications\ActiviteValidee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ValidationController extends Controller
{
    public function index(Request $request)
    {
        $query = $this->buildQuery($request);
        $activites = $query->with(['extrant.objectif', 'departement', 'saisiePar', 'validationHistoriques.utilisateur'])
            ->orderBy('date_soumission', 'desc')
            ->paginate(20)
            ->withQueryString();

        $departements = $this->availableDepartements();
        $extrants = Extrant::with('objectif')->actif()->ordered()->get();
        $compteur = $this->buildQuery($request)->get()->count();

        return view('pages.validations.index', compact('activites', 'departements', 'extrants', 'compteur'));
    }

    public function show(Activite $activite)
    {
        if (! $this->canValidate($activite)) {
            abort(403);
        }

        if ($activite->statut !== 'soumis') {
            return redirect()->route('validations.index')
                ->with('error', 'Cette activité n\'est pas en attente de validation.');
        }

        $activite->load(['extrant.objectif', 'departement', 'saisiePar', 'validationHistoriques.utilisateur']);

        return view('pages.validations.show', compact('activite'));
    }

    public function valider(Request $request, Activite $activite)
    {
        $request->validate([
            'commentaire' => 'nullable|string|max:1000',
        ]);

        if (! $this->canValidate($activite)) {
            abort(403);
        }

        if (! $activite->peutEtreValide()) {
            return back()->with('error', 'Cette activité ne peut pas être validée.');
        }

        $activite->valider($request->input('commentaire'));

        if ($activite->saisiePar) {
            $activite->saisiePar->notify(new ActiviteValidee($activite, $request->input('commentaire')));
        }

        return redirect()->route('validations.index')
            ->with('success', 'Activité validée avec succès.');
    }

    public function refuser(Request $request, Activite $activite)
    {
        $validated = $request->validate([
            'motif_refus' => 'required|string|min:10|max:1000',
            'notifier_utilisateur' => 'sometimes|accepted',
        ]);

        if (! $this->canValidate($activite)) {
            abort(403);
        }

        if (! $activite->peutEtreValide()) {
            return back()->with('error', 'Cette activité ne peut pas être refusée.');
        }

        $activite->refuser($validated['motif_refus']);

        if (! empty($validated['notifier_utilisateur']) && $activite->saisiePar) {
            $activite->saisiePar->notify(new ActiviteRefusee($activite, $validated['motif_refus']));
        }

        return redirect()->route('validations.index')
            ->with('success', 'Activité refusée avec succès.');
    }

    public function validerPlusieurs(Request $request)
    {
        $validated = $request->validate([
            'activite_ids' => 'required|array|min:1',
            'activite_ids.*' => 'integer|exists:activites,id',
        ]);

        $activites = Activite::query()->soumis()->whereKey($validated['activite_ids'])->get()
            ->filter(fn (Activite $activite) => $this->canValidate($activite));

        if ($activites->isEmpty()) {
            return redirect()->route('validations.index')
                ->with('error', 'Aucune activité sélectionnée ne peut être validée par votre profil.');
        }

        foreach ($activites as $activite) {
            $activite->valider();
            if ($activite->saisiePar) {
                $activite->saisiePar->notify(new ActiviteValidee($activite));
            }
        }

        return redirect()->route('validations.index')
            ->with('success', 'Sélection des activités validée avec succès.');
    }

    /**
     * Arbitrage budgétaire — Modifier une activité avant validation.
     */
    public function arbitrerModifier(Request $request, Activite $activite)
    {
        if (! $this->canValidate($activite) || $activite->statut !== 'soumis') {
            abort(403);
        }

        $validated = $request->validate($this->reglesArbitrage() + [
            'motif' => 'nullable|string|max:1000',
        ]);

        $activite->arbitrerModification(
            collect($validated)->except('motif')->all(),
            $validated['motif'] ?? null
        );

        $this->notifierArbitrage($activite->saisiePar, 'modifiee', $activite->nom_activite, $validated['motif'] ?? null);

        return redirect()->route('validations.index')
            ->with('success', 'Activité modifiée et l\'auteur a été notifié.');
    }

    /**
     * Arbitrage budgétaire — Supprimer (archiver) une activité avant validation.
     */
    public function arbitrerSupprimer(Request $request, Activite $activite)
    {
        if (! $this->canValidate($activite) || $activite->statut !== 'soumis') {
            abort(403);
        }

        $validated = $request->validate([
            'motif' => 'required|string|min:5|max:1000',
        ]);

        $saisiPar = $activite->saisiePar;
        $nom = $activite->nom_activite;

        $activite->arbitrerSuppression($validated['motif']);

        $this->notifierArbitrage($saisiPar, 'supprimee', $nom, $validated['motif']);

        return redirect()->route('validations.index')
            ->with('success', 'Activité supprimée et l\'auteur a été notifié.');
    }

    /**
     * Arbitrage budgétaire — Fusionner plusieurs activités en une activité consolidée.
     */
    public function arbitrerFusionner(Request $request)
    {
        $validated = $request->validate($this->reglesArbitrage() + [
            'activite_ids' => 'required|array|min:2',
            'activite_ids.*' => 'integer|exists:activites,id',
            'extrant_id' => 'required|integer|exists:extrants,id',
            'departement_id' => 'required|integer|exists:departements,id',
            'motif' => 'nullable|string|max:1000',
        ]);

        $sources = Activite::query()->soumis()->whereKey($validated['activite_ids'])->get()
            ->filter(fn (Activite $a) => $this->canValidate($a));

        if ($sources->count() < 2) {
            return redirect()->route('validations.index')
                ->with('error', 'Sélectionnez au moins deux activités fusionnables de votre périmètre.');
        }

        $consolidee = DB::transaction(function () use ($validated, $sources) {
            $premiere = $sources->first();

            $consolidee = Activite::create([
                'extrant_id' => $validated['extrant_id'],
                'departement_id' => $validated['departement_id'],
                'nom_activite' => $validated['nom_activite'],
                'indicateur_objectivement_verifiable' => $validated['indicateur_objectivement_verifiable'],
                'moyen_verification' => $validated['moyen_verification'],
                'cout' => $validated['cout'],
                'trimestre_1' => $validated['trimestre_1'],
                'trimestre_2' => $validated['trimestre_2'],
                'trimestre_3' => $validated['trimestre_3'],
                'trimestre_4' => $validated['trimestre_4'],
                'statut' => 'soumis',
                'saisi_par' => $premiere->saisi_par,
                'date_saisie' => now(),
                'date_soumission' => now(),
            ]);

            $consolidee->journaliserArbitrage('arbitrage_fusion', $validated['motif'] ?? null);

            foreach ($sources as $source) {
                $source->arbitrerSuppression($validated['motif'] ?? null);
            }

            return $consolidee;
        });

        // Notifier chaque auteur d'activité source (dédoublonné par utilisateur).
        $sources->groupBy('saisi_par')->each(function ($groupe) use ($validated, $consolidee) {
            $auteur = $groupe->first()->saisiePar;
            foreach ($groupe as $source) {
                $this->notifierArbitrage($auteur, 'fusionnee', $source->nom_activite, $validated['motif'] ?? null, $consolidee->nom_activite);
            }
        });

        return redirect()->route('validations.index')
            ->with('success', $sources->count().' activités fusionnées et les auteurs ont été notifiés.');
    }

    private function reglesArbitrage(): array
    {
        return [
            'nom_activite' => 'required|string|max:1000',
            'indicateur_objectivement_verifiable' => 'required|string|max:1000',
            'moyen_verification' => 'required|string|max:1000',
            'cout' => 'required|numeric|min:0',
            'trimestre_1' => ['required', Rule::in(['oui', 'non'])],
            'trimestre_2' => ['required', Rule::in(['oui', 'non'])],
            'trimestre_3' => ['required', Rule::in(['oui', 'non'])],
            'trimestre_4' => ['required', Rule::in(['oui', 'non'])],
        ];
    }

    private function notifierArbitrage($auteur, string $action, string $nom, ?string $motif, ?string $nomConsolidee = null): void
    {
        if ($auteur) {
            $auteur->notify(new ActiviteArbitrageNotification($action, $nom, $motif, $nomConsolidee));
        }
    }

    public function exporter(Request $request)
    {
        $query = $this->buildQuery($request)->with(['extrant.objectif', 'departement', 'saisiePar']);

        $filename = 'activites-en-attente-' . now()->format('Ymd_His') . '.csv';

        $response = new StreamedResponse(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Code activité',
                'Nom activité',
                'Département',
                'Extrant',
                'Objectif',
                'Date de soumission',
                'Coût',
                'Indicateur',
                'Saisi par',
                'Statut',
            ]);

            $query->chunk(100, function ($activites) use ($handle) {
                foreach ($activites as $activite) {
                    fputcsv($handle, [
                        'ACT-' . $activite->id,
                        $activite->nom_activite,
                        $activite->departement->nom ?? 'N/A',
                        $activite->extrant->code ?? 'N/A',
                        $activite->extrant->objectif->code ?? 'N/A',
                        optional($activite->date_soumission)->format('d/m/Y H:i'),
                        number_format($activite->cout, 0, ',', ' '),
                        $activite->indicateur_objectivement_verifiable,
                        $activite->saisiePar->name ?? 'N/A',
                        $activite->statut,
                    ]);
                }
            });

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', "attachment; filename=\"{$filename}\"");

        return $response;
    }

    private function buildQuery(Request $request)
    {
        $query = Activite::query()->soumis();

        if (Auth::user()?->hasRole('chef_departement') && Auth::user()?->departement_id) {
            $query->where('departement_id', Auth::user()->departement_id);
        }

        if ($request->filled('departement_id')) {
            $query->where('departement_id', $request->departement_id);
        }

        if ($request->filled('extrant_id')) {
            $query->where('extrant_id', $request->extrant_id);
        }

        if ($request->filled('trimestre')) {
            $query->pourTrimestre($request->trimestre);
        }

        if ($request->filled('date_from')) {
            $query->where('date_soumission', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('date_soumission', '<=', $request->date_to);
        }

        return $query;
    }

    private function availableDepartements()
    {
        $query = Departement::active()->ordered();

        if (Auth::user()?->hasRole('chef_departement') && Auth::user()?->departement_id) {
            $query->whereKey(Auth::user()->departement_id);
        }

        return $query->get();
    }

    private function canValidate(Activite $activite): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        if ($user->hasRole('dbcgoq')) {
            return true;
        }

        return $user->hasRole('chef_departement')
            && $user->departement_id !== null
            && (int) $user->departement_id === (int) $activite->departement_id;
    }
}
