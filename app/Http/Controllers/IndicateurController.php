<?php

namespace App\Http\Controllers;

use App\Models\Indicateur;
use App\Models\IndicateurValeur;
use App\Models\Objectif;
use App\Models\Departement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class IndicateurController extends Controller
{
    /**
     * Liste des indicateurs
     */
    public function index(Request $request)
    {
        $query = Indicateur::with('objectif');

        if ($request->filled('objectif_id')) {
            $query->where('objectif_id', $request->objectif_id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('code', 'like', "%{$request->search}%")
                    ->orWhere('libelle', 'like', "%{$request->search}%");
            });
        }

        $indicateurs = $query->ordered()->paginate(15)->withQueryString();

        $objectifs = Objectif::where('statut', 'actif')->orderBy('annee', 'desc')->get();
        $types = ['performance', 'gestion', 'qualite', 'efficacite'];

        return view('pages.indicateurs.index', compact('indicateurs', 'objectifs', 'types'));
    }

    /**
     * Formulaire de création
     */
    public function create(Request $request)
    {
        $objectifs = Objectif::where('statut', 'actif')->orderBy('annee', 'desc')->get();
        $periodicites = ['mensuel', 'trimestriel', 'semestriel', 'annuel'];
        $types = ['performance', 'gestion', 'qualite', 'efficacite'];
        $selectedObjectif = $request->get('objectif_id');

        return view('pages.indicateurs.create', compact('objectifs', 'periodicites', 'types', 'selectedObjectif'));
    }

    /**
     * Enregistrement
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'objectif_id' => 'required|exists:objectifs,id',
            'code' => 'required|string|max:50|unique:indicateurs',
            'libelle' => 'required|string|max:500',
            'description' => 'nullable|string',
            'type' => ['required', Rule::in(['performance', 'gestion', 'qualite', 'efficacite'])],
            'unite' => 'nullable|string|max:50',
            'formule_calcul' => 'nullable|string',
            'cible' => 'nullable|numeric|min:0',
            'seuil_alerte' => 'nullable|numeric|min:0',
            'periodicite' => ['required', Rule::in(['mensuel', 'trimestriel', 'semestriel', 'annuel'])],
            'sens' => ['required', Rule::in(['hausse', 'baisse'])],
            'source_donnee' => 'nullable|string|max:200',
            'ordre' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $indicateur = Indicateur::create($validated);

        return redirect()->route('indicateurs.index')
            ->with('success', "Indicateur {$indicateur->code} créé avec succès.");
    }

    /**
     * Détail d'un indicateur
     */
    public function show(Indicateur $indicateur)
    {
        $indicateur->load(['objectif', 'valeurs.departement', 'valeurs.saisiPar']);

        $stats = [
            'nb_valeurs' => $indicateur->valeurs->count(),
            'derniere_valeur' => $indicateur->valeurs()->orderBy('periode', 'desc')->first(),
            'taux_moyen' => $indicateur->valeurs()->avg('taux_realisation'),
        ];

        return view('pages.indicateurs.show', compact('indicateur', 'stats'));
    }

    /**
     * Formulaire d'édition
     */
    public function edit(Indicateur $indicateur)
    {
        $objectifs = Objectif::where('statut', 'actif')->orderBy('annee', 'desc')->get();
        $periodicites = ['mensuel', 'trimestriel', 'semestriel', 'annuel'];
        $types = ['performance', 'gestion', 'qualite', 'efficacite'];

        return view('pages.indicateurs.edit', compact('indicateur', 'objectifs', 'periodicites', 'types'));
    }

    /**
     * Mise à jour
     */
    public function update(Request $request, Indicateur $indicateur)
    {
        $validated = $request->validate([
            'objectif_id' => 'required|exists:objectifs,id',
            'code' => ['required', 'string', 'max:50', Rule::unique('indicateurs')->ignore($indicateur->id)],
            'libelle' => 'required|string|max:500',
            'description' => 'nullable|string',
            'type' => ['required', Rule::in(['performance', 'gestion', 'qualite', 'efficacite'])],
            'unite' => 'nullable|string|max:50',
            'formule_calcul' => 'nullable|string',
            'cible' => 'nullable|numeric|min:0',
            'seuil_alerte' => 'nullable|numeric|min:0',
            'periodicite' => ['required', Rule::in(['mensuel', 'trimestriel', 'semestriel', 'annuel'])],
            'sens' => ['required', Rule::in(['hausse', 'baisse'])],
            'source_donnee' => 'nullable|string|max:200',
            'ordre' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $indicateur->update($validated);

        return redirect()->route('indicateurs.index')
            ->with('success', "Indicateur {$indicateur->code} mis à jour.");
    }

    /**
     * Suppression
     */
    public function destroy(Indicateur $indicateur)
    {
        if ($indicateur->valeurs()->count() > 0) {
            return redirect()->route('indicateurs.index')
                ->with('error', 'Impossible de supprimer un indicateur qui a des valeurs.');
        }

        $code = $indicateur->code;
        $indicateur->delete();

        return redirect()->route('indicateurs.index')
            ->with('success', "Indicateur {$code} supprimé.");
    }

    /**
     * Activer/Désactiver
     */
    public function toggleStatus(Indicateur $indicateur)
    {
        $indicateur->update(['is_active' => !$indicateur->is_active]);

        $status = $indicateur->is_active ? 'activé' : 'désactivé';

        return redirect()->route('indicateurs.index')
            ->with('success', "Indicateur {$indicateur->code} {$status}.");
    }

    /**
     * Saisie des valeurs
     */
    public function saisieValeurs(Indicateur $indicateur)
    {
        $departements = Departement::active()->ordered()->get();
        $periodes = $this->genererPeriodes($indicateur->periodicite);

        return view('pages.indicateurs.saisie-valeurs', compact('indicateur', 'departements', 'periodes'));
    }

    /**
     * Enregistrement des valeurs
     */
    public function storeValeurs(Request $request, Indicateur $indicateur)
    {
        $validated = $request->validate([
            'valeurs' => 'required|array',
            'valeurs.*.departement_id' => 'required|exists:departements,id',
            'valeurs.*.periode' => 'required|string',
            'valeurs.*.valeur_realisee' => 'required|numeric|min:0',
            'valeurs.*.commentaire' => 'nullable|string',
        ]);

        foreach ($validated['valeurs'] as $valeur) {
            $indicateurValeur = IndicateurValeur::updateOrCreate(
                [
                    'indicateur_id' => $indicateur->id,
                    'departement_id' => $valeur['departement_id'],
                    'periode' => $valeur['periode'],
                ],
                [
                    'valeur_realisee' => $valeur['valeur_realisee'],
                    'commentaire' => $valeur['commentaire'] ?? null,
                    'saisi_par' => Auth::id(),
                    'date_saisie' => now(),
                    'statut' => 'brouillon',
                ]
            );

            $indicateurValeur->calculerEcart();
            $indicateurValeur->save();
        }

        return redirect()->route('indicateurs.show', $indicateur)
            ->with('success', 'Valeurs enregistrées avec succès.');
    }

    /**
     * Générer les périodes
     */
    private function genererPeriodes($periodicite)
    {
        $periodes = [];
        $currentYear = date('Y');

        switch ($periodicite) {
            case 'mensuel':
                for ($i = 1; $i <= 12; $i++) {
                    $periodes[] = sprintf('%02d_%d', $i, $currentYear);
                }
                break;
            case 'trimestriel':
                for ($i = 1; $i <= 4; $i++) {
                    $periodes[] = "T{$i}_{$currentYear}";
                }
                break;
            case 'semestriel':
                $periodes[] = "S1_{$currentYear}";
                $periodes[] = "S2_{$currentYear}";
                break;
            case 'annuel':
                $periodes[] = "A_{$currentYear}";
                break;
        }

        return $periodes;
    }
}
