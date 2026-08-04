<?php

namespace App\Http\Controllers;

use App\Models\Activite;
use App\Models\Departement;
use App\Notifications\ActiviteArbitrageNotification;
use App\Notifications\ActiviteCoutModifieNotification;
use App\Notifications\ActiviteRefusee;
use App\Notifications\ActiviteValidee;
use App\Support\ActiveExercice;
use App\Support\CadreLogique;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ValidationController extends Controller
{
    public function index(Request $request)
    {
        $activites = $this->buildQuery($request)
            ->with(['extrant.objectif', 'departement.parent.parent', 'saisiePar', 'validationHistoriques.utilisateur'])
            ->orderBy('date_soumission', 'desc')
            ->get();

        // Un service n'est jamais arbitré pour lui-même : ses activités sont concentrées
        // dans l'entité qui le chapeaute (ex. SJC → DAGRH). Les autres entités sont
        // arbitrées pour elles-mêmes.
        $groupes = $activites
            ->groupBy(fn ($activite) => $activite->departement?->entiteDeRattachement()?->id)
            ->map(function ($groupe) {
                $porteuse = $groupe->first()->departement;
                $entite = $porteuse?->entiteDeRattachement();

                return [
                    'departement' => $entite,
                    'activites' => $groupe->values(),
                    'cout_total' => (float) $groupe->sum('cout'),
                ];
            })
            ->sortBy(fn ($groupe) => $groupe['departement']->nom ?? '', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        // Sections par niveau hiérarchique : Directions, Directions Centrales, Services.
        $sections = collect(Departement::TYPES)
            ->mapWithKeys(fn ($type) => [
                $type => $groupes->filter(fn ($groupe) => ($groupe['departement']->type ?? null) === $type)->values(),
            ])
            ->put('autres', $groupes->filter(
                fn ($groupe) => ! in_array($groupe['departement']->type ?? null, Departement::TYPES, true)
            )->values())
            ->filter(fn ($section) => $section->isNotEmpty());

        $compteur = $activites->count();
        $coutTotal = (float) $activites->sum('cout');

        return view('pages.validations.index', compact('groupes', 'sections', 'compteur', 'coutTotal'));
    }

    /**
     * Arbitrage détaillé d'une entité : toutes ses activités soumises, avec les
     * actions groupées (fusion, validation par sélection) et l'export du cadre logique.
     */
    public function entite(Request $request, Departement $departement)
    {
        // Une Direction Centrale arbitre pour tout son sous-arbre : les activités de ses
        // services y sont concentrées. Les activités déjà validées restent affichées afin
        // que la date de validation soit consultable ; seules les activités en attente
        // ouvrent les actions d'arbitrage.
        $perimetre = $departement->sousArbreIds();

        $activites = $this->appliquerPerimetre(
                Activite::query()->whereIn('statut', ['en_attente', 'valide']),
                $request
            )
            ->whereIn('departement_id', $perimetre)
            ->with(['extrant.resultat', 'extrant.objectif', 'departement.responsable', 'departement.parent', 'saisiePar', 'validePar', 'validationHistoriques.utilisateur'])
            ->orderBy('date_soumission', 'desc')
            ->get();

        // Présentation en cadre logique : Résultat stratégique → Extrant → activités,
        // dans l'ordre défini sur les résultats puis les extrants.
        $resultats = $activites
            ->groupBy(fn ($activite) => optional($activite->extrant)->resultat_id)
            ->map(function ($parResultat) {
                $resultat = optional($parResultat->first()->extrant)->resultat;

                $extrantsGroupes = $parResultat
                    ->groupBy('extrant_id')
                    ->map(fn ($parExtrant) => [
                        'extrant' => $parExtrant->first()->extrant,
                        // Au sein d'un extrant, les activités sont classées par service
                        // porteur (ordre alphabétique) puis par identifiant. La clé de tri
                        // est une chaîne unique : `sortBy` ne sait pas comparer des tableaux.
                        'activites' => $parExtrant
                            ->sortBy(fn ($activite) => mb_strtolower($activite->departement->nom ?? '')
                                .'|'.str_pad((string) $activite->id, 12, '0', STR_PAD_LEFT))
                            ->values(),
                        'cout_total' => (float) $parExtrant->where('statut', 'en_attente')->sum('cout'),
                    ])
                    ->sortBy(fn ($bloc) => sprintf('%010d|%s', $bloc['extrant']->ordre ?? PHP_INT_MAX, $bloc['extrant']->code ?? ''))
                    ->values();

                return [
                    'resultat' => $resultat,
                    'extrants' => $extrantsGroupes,
                    'cout_total' => (float) $parResultat->where('statut', 'en_attente')->sum('cout'),
                    'nb_activites' => $parResultat->where('statut', 'en_attente')->count(),
                ];
            })
            ->sortBy(fn ($bloc) => sprintf(
                '%010d|%010d|%s',
                $bloc['resultat']->objectif_id ?? PHP_INT_MAX,
                $bloc['resultat']->ordre ?? PHP_INT_MAX,
                $bloc['resultat']->code ?? ''
            ))
            ->values();

        $departement->load('parent', 'responsable');

        $enAttente = $activites->where('statut', 'en_attente');
        $coutTotal = (float) $enAttente->sum('cout');
        $nbEnAttente = $enAttente->count();
        $nbValidees = $activites->where('statut', 'valide')->count();

        return view('pages.validations.entite', compact(
            'departement', 'activites', 'resultats', 'coutTotal', 'nbEnAttente', 'nbValidees'
        ));
    }

    public function show(Activite $activite)
    {
        if (! $this->canValidate($activite)) {
            abort(403);
        }

        if ($activite->statut !== 'en_attente') {
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

        return redirect()->route('validations.entite', $activite->departement_id)
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

        return redirect()->route('validations.entite', $activite->departement_id)
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
            return back()->with('error', 'Aucune activité sélectionnée ne peut être validée par votre profil.');
        }

        foreach ($activites as $activite) {
            $activite->valider();
            if ($activite->saisiePar) {
                $activite->saisiePar->notify(new ActiviteValidee($activite));
            }
        }

        return back()->with('success', 'Sélection des activités validée avec succès.');
    }

    /**
     * Arbitrage budgétaire — Modifier une activité avant validation.
     */
    public function arbitrerModifier(Request $request, Activite $activite)
    {
        if (! $this->canValidate($activite) || $activite->statut !== 'en_attente') {
            abort(403);
        }

        $validated = $request->validate($this->reglesArbitrage() + [
            'motif' => 'nullable|string|max:1000',
        ]);

        $ancienCout = (float) $activite->cout;

        $activite->arbitrerModification(
            collect($validated)->except('motif')->all(),
            $validated['motif'] ?? null
        );

        $this->notifierArbitrage($activite->saisiePar, 'modifiee', $activite->nom_activite, $validated['motif'] ?? null, null, $activite);

        // Le directeur de la Direction Centrale et le chef de service sont informés
        // de tout changement de budget décidé lors de l'arbitrage.
        $nouveauCout = (float) $activite->fresh()->cout;
        if ($ancienCout !== $nouveauCout) {
            foreach ($activite->destinatairesChangementBudget() as $destinataire) {
                $destinataire->notify(new ActiviteCoutModifieNotification($activite, $ancienCout, $nouveauCout, 'arbitrage', $validated['motif'] ?? null));
            }
        }

        return redirect()->route('validations.entite', $activite->departement_id)
            ->with('success', 'Activité modifiée et l\'auteur a été notifié.');
    }

    /**
     * Arbitrage budgétaire — Supprimer (archiver) une activité avant validation.
     */
    public function arbitrerSupprimer(Request $request, Activite $activite)
    {
        if (! $this->canValidate($activite) || $activite->statut !== 'en_attente') {
            abort(403);
        }

        $validated = $request->validate([
            'motif' => 'required|string|min:5|max:1000',
        ]);

        $saisiPar = $activite->saisiePar;
        $nom = $activite->nom_activite;

        $activite->arbitrerSuppression($validated['motif']);

        $this->notifierArbitrage($saisiPar, 'supprimee', $nom, $validated['motif']);

        return redirect()->route('validations.entite', $activite->departement_id)
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
            return back()->with('error', 'Sélectionnez au moins deux activités fusionnables de votre périmètre.');
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
                'statut' => 'en_attente',
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
                $this->notifierArbitrage($auteur, 'fusionnee', $source->nom_activite, $validated['motif'] ?? null, $consolidee->nom_activite, $consolidee);
            }
        });

        return back()->with('success', $sources->count().' activités fusionnées et les auteurs ont été notifiés.');
    }

    private function reglesArbitrage(): array
    {
        return [
            'nom_activite' => 'required|string|max:1000',
            'indicateur_objectivement_verifiable' => 'required|string|max:1000',
            'moyen_verification' => 'required|string|max:1000',
            'cout' => 'required|numeric|min:0|max:'.Activite::MONTANT_MAX,
            'trimestre_1' => ['required', Rule::in(['oui', 'non'])],
            'trimestre_2' => ['required', Rule::in(['oui', 'non'])],
            'trimestre_3' => ['required', Rule::in(['oui', 'non'])],
            'trimestre_4' => ['required', Rule::in(['oui', 'non'])],
        ];
    }

    private function notifierArbitrage($auteur, string $action, string $nom, ?string $motif, ?string $nomConsolidee = null, ?Activite $cible = null): void
    {
        if ($auteur) {
            $auteur->notify(new ActiviteArbitrageNotification($action, $nom, $motif, $nomConsolidee, $cible));
        }
    }

    public function exporter(Request $request)
    {
        // L'export couvre toutes les activités de l'exercice en cours (tous statuts),
        // pas seulement celles en attente d'arbitrage.
        $query = Activite::query()->forExercice(ActiveExercice::id());

        $activites = $this->appliquerPerimetre($query, $request)
            ->with([
                'extrant.resultat.objectif',
                'departement:id,code,nom',
                'departements:id,code,nom',
            ])
            ->get();

        // Cadre logique d'arbitrage : Objectif → Résultat stratégique → Extrant.
        $objectifs = CadreLogique::grouper($activites);

        $html = view('pages.validations.export-arbitrage', compact('objectifs'))->render();

        $filename = 'cadre-logique-arbitrage-'.now()->format('Ymd_His').'.xls';

        return response($html, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function buildQuery(Request $request)
    {
        return $this->appliquerPerimetre(Activite::query()->soumis(), $request);
    }

    /**
     * Restreint une requête au périmètre d'arbitrage de l'utilisateur, puis applique
     * les filtres de la requête HTTP.
     */
    private function appliquerPerimetre($query, Request $request)
    {
        $user = Auth::user();

        // Le superadmin et le dbcgoq arbitrent l'ensemble des entités ; un chef ne voit
        // que les soumissions des entités qu'il chapeaute (flux montant).
        if ($user && ! $user->hasRole('superadmin') && ! $user->hasRole('dbcgoq')
            && $user->isChef() && $user->departement_id) {
            $query->whereIn('departement_id', $user->entitesSupervisees()->pluck('id'));
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

        return $query;
    }

    private function canValidate(Activite $activite): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        if ($user->hasRole('superadmin') || $user->hasRole('dbcgoq')) {
            return true;
        }

        // Flux montant : le chef valide les activités des entités enfants de la sienne.
        $entite = $activite->relationLoaded('departement')
            ? $activite->departement
            : $activite->departement()->first();

        return $user->hasRole('chef')
            && $user->departement_id !== null
            && $entite?->parent_id !== null
            && (int) $entite->parent_id === (int) $user->departement_id;
    }
}
