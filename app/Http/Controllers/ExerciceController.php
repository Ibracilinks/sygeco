<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExerciceRequest;
use App\Http\Requests\UpdateExerciceRequest;
use App\Models\Activite;
use App\Models\Exercice;
use App\Models\Objectif;
use App\Models\Resultat;
use App\Support\ActiveExercice;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class ExerciceController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->authorizeResource(Exercice::class, 'exercice');
    }

    public function index(Request $request)
    {
        $query = Exercice::query()->ordered();

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        $exercices = $query->withCount('objectifs')->paginate(15)->withQueryString();

        $summary = [
            'total' => Exercice::count(),
            'actif' => Exercice::where('statut', 'actif')->count(),
            'cloture' => Exercice::where('statut', 'cloture')->count(),
            'brouillon' => Exercice::where('statut', 'brouillon')->count(),
        ];

        return view('pages.exercices.index', compact('exercices', 'summary'));
    }

    public function create()
    {
        return view('pages.exercices.create');
    }

    public function store(StoreExerciceRequest $request)
    {
        $data = $request->validated();
        $this->ensureSingleActif($data['statut'] ?? null);

        Exercice::create($data);

        return redirect()->route('exercices.index')
            ->with('success', 'Exercice créé.');
    }

    public function show(Exercice $exercice)
    {
        $exercice->loadCount('objectifs');

        $objectifs = Objectif::where('exercice_id', $exercice->id)
            ->withCount(['resultats', 'extrants'])
            ->orderBy('ordre')
            ->orderBy('code')
            ->get();

        $stats = [
            'nb_objectifs' => $exercice->objectifs_count,
            'nb_resultats' => (int) $objectifs->sum('resultats_count'),
            'nb_extrants' => (int) $objectifs->sum('extrants_count'),
            'nb_activites' => Activite::forExercice($exercice->id)->count(),
            'budget_total' => (float) Activite::forExercice($exercice->id)->sum('cout'),
            'activites_par_statut' => [
                'brouillon' => Activite::forExercice($exercice->id)->where('statut', 'brouillon')->count(),
                'soumis' => Activite::forExercice($exercice->id)->where('statut', 'soumis')->count(),
                'valide' => Activite::forExercice($exercice->id)->where('statut', 'valide')->count(),
            ],
            'execution' => [
                'non_realise' => Activite::forExercice($exercice->id)->where('statut_execution', 'non_realise')->count(),
                'en_cours' => Activite::forExercice($exercice->id)->where('statut_execution', 'en_cours')->count(),
                'realise' => Activite::forExercice($exercice->id)->where('statut_execution', 'realise')->count(),
            ],
        ];

        $stats['taux_realisation'] = $stats['nb_activites'] > 0
            ? round($stats['execution']['realise'] / $stats['nb_activites'] * 100, 1)
            : 0.0;

        $relances = $exercice->relances()->orderByDesc('palier')->get();

        $charts = $this->buildExerciceCharts($exercice, $objectifs, $stats);

        $isActiveContext = ActiveExercice::id() === (int) $exercice->id;

        return view('pages.exercices.show', compact('exercice', 'objectifs', 'stats', 'relances', 'charts', 'isActiveContext'));
    }

    /**
     * Construit l'ensemble des séries graphiques (objectifs, résultats, extrants, activités) d'un exercice.
     */
    protected function buildExerciceCharts(Exercice $exercice, $objectifs, array $stats): array
    {
        $activites = Activite::forExercice($exercice->id)
            ->with('extrant:id,code,objectif_id,resultat_id')
            ->get(['id', 'cout', 'statut', 'trimestre_1', 'trimestre_2', 'trimestre_3', 'trimestre_4', 'extrant_id']);

        $objectifCodes = $objectifs->pluck('code', 'id');

        $resultats = Resultat::whereIn('objectif_id', $objectifs->pluck('id'))
            ->withCount('extrants')
            ->orderBy('ordre')
            ->orderBy('code')
            ->get();
        $resultatCodes = $resultats->pluck('code', 'id');

        // Budget (M FCFA) par objectif
        $budgetParObjectif = $activites->groupBy(fn ($a) => $a->extrant?->objectif_id)
            ->map(fn ($g) => round($g->sum('cout') / 1_000_000, 2));

        // Budget (M FCFA) par résultat stratégique
        $budgetParResultat = $activites->groupBy(fn ($a) => $a->extrant?->resultat_id)
            ->map(fn ($g) => round($g->sum('cout') / 1_000_000, 2));

        // Nombre d'activités par extrant (top 10)
        $actParExtrant = $activites->groupBy(fn ($a) => $a->extrant?->code ?? '—')
            ->map->count()
            ->sortDesc()
            ->take(10);

        // Planification trimestrielle
        $trimestres = [
            'T1' => $activites->where('trimestre_1', 'oui')->count(),
            'T2' => $activites->where('trimestre_2', 'oui')->count(),
            'T3' => $activites->where('trimestre_3', 'oui')->count(),
            'T4' => $activites->where('trimestre_4', 'oui')->count(),
        ];

        // Distribution budgétaire par tranche de coût
        $tranches = ['< 5M' => 0, '5–20M' => 0, '20–50M' => 0, '> 50M' => 0];
        foreach ($activites as $a) {
            $m = (float) $a->cout / 1_000_000;
            match (true) {
                $m < 5 => $tranches['< 5M']++,
                $m < 20 => $tranches['5–20M']++,
                $m < 50 => $tranches['20–50M']++,
                default => $tranches['> 50M']++,
            };
        }

        return [
            'structure' => [
                'labels' => ['Objectifs', 'Résultats', 'Extrants', 'Activités'],
                'values' => [$stats['nb_objectifs'], $stats['nb_resultats'], $stats['nb_extrants'], $stats['nb_activites']],
            ],
            'budget_par_objectif' => [
                'labels' => $budgetParObjectif->keys()->map(fn ($id) => $objectifCodes[$id] ?? '—')->values()->all(),
                'values' => $budgetParObjectif->values()->all(),
            ],
            'budget_par_resultat' => [
                'labels' => $budgetParResultat->keys()->map(fn ($id) => $resultatCodes[$id] ?? '—')->values()->all(),
                'values' => $budgetParResultat->values()->all(),
            ],
            'extrants_par_resultat' => [
                'labels' => $resultats->pluck('code')->all(),
                'values' => $resultats->pluck('extrants_count')->all(),
            ],
            'activites_par_extrant' => [
                'labels' => $actParExtrant->keys()->all(),
                'values' => $actParExtrant->values()->all(),
            ],
            'activites_statut' => [
                'labels' => ['Brouillon', 'Soumis', 'Validé'],
                'values' => array_values($stats['activites_par_statut']),
            ],
            'avancement' => [
                'labels' => ['Réalisé', 'En cours', 'Non réalisé'],
                'values' => [
                    $stats['execution']['realise'],
                    $stats['execution']['en_cours'],
                    $stats['execution']['non_realise'],
                ],
            ],
            'activites_trimestre' => [
                'labels' => array_keys($trimestres),
                'values' => array_values($trimestres),
            ],
            'distribution_budgetaire' => [
                'labels' => array_keys($tranches),
                'values' => array_values($tranches),
            ],
        ];
    }

    public function edit(Exercice $exercice)
    {
        return view('pages.exercices.edit', compact('exercice'));
    }

    /**
     * Exporte le PTA de l'exercice (Objectif → Résultat → Extrant → Activités)
     * au format tableau, ouvrable dans Excel / LibreOffice.
     */
    public function export(Exercice $exercice)
    {
        $this->authorize('view', $exercice);

        $objectifs = Objectif::where('exercice_id', $exercice->id)
            ->orderBy('ordre')
            ->orderBy('code')
            ->with(['resultats' => function ($q) {
                $q->orderBy('ordre')->orderBy('code')
                    ->with(['extrants' => function ($e) {
                        $e->orderBy('ordre')->orderBy('code')
                            ->with(['activites' => function ($a) {
                                $a->with(['departement:id,code,nom', 'departements:id,code,nom'])->orderBy('id');
                            }]);
                    }]);
            }])
            ->get();

        $html = view('pages.exercices.export', compact('exercice', 'objectifs'))->render();

        $filename = 'PTA_exercice_' . $exercice->annee . '_' . now()->format('Ymd') . '.xls';

        return response($html, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function update(UpdateExerciceRequest $request, Exercice $exercice)
    {
        $data = $request->validated();
        $this->ensureSingleActif($data['statut'] ?? null, $exercice->id);

        $exercice->update($data);

        return redirect()->route('exercices.index')
            ->with('success', 'Exercice mis à jour.');
    }

    public function destroy(Exercice $exercice)
    {
        if ($exercice->objectifs()->exists()) {
            return redirect()->route('exercices.index')
                ->with('error', 'Impossible de supprimer un exercice lié à des objectifs.');
        }

        $exercice->delete();

        if (ActiveExercice::id() === (int) $exercice->id) {
            ActiveExercice::set(null);
        }

        return redirect()->route('exercices.index')
            ->with('success', 'Exercice supprimé.');
    }

    public function activate(Exercice $exercice)
    {
        $this->authorize('view', $exercice);

        ActiveExercice::set((int) $exercice->id);

        return redirect()->back()
            ->with('success', 'Exercice actif mis à jour pour cette session.');
    }

    protected function ensureSingleActif(?string $statut, ?int $exceptId = null): void
    {
        if ($statut !== 'actif') {
            return;
        }

        $q = Exercice::query()->where('statut', 'actif');
        if ($exceptId) {
            $q->where('id', '!=', $exceptId);
        }
        $q->update(['statut' => 'cloture']);
    }
}
