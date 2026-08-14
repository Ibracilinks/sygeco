<?php

namespace App\Http\Controllers;

use App\Models\Extrant;
use App\Models\Objectif;
use App\Models\Resultat;
use App\Support\ActiveExercice;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExtrantController extends Controller
{
    /**
     * Liste des extrants
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Extrant::query()
            ->with('objectif')
            ->withCount('activites');

        $exerciceId = ActiveExercice::id();
        if ($exerciceId !== null) {
            $query->whereHas('objectif.exercices', fn ($q) => $q->where('exercices.id', $exerciceId));
        }

        if ($this->isChefDepartement($user)) {
            $this->applyDepartmentScopeToExtrantQuery($query, (int) $user->departement_id);
        }

        if ($request->filled('objectif_id')) {
            $query->where('objectif_id', $request->objectif_id);
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

        $summaryQuery = clone $query;
        $summary = [
            'total' => (clone $summaryQuery)->count(),
            'actifs' => (clone $summaryQuery)->where('is_active', true)->count(),
            'inactifs' => (clone $summaryQuery)->where('is_active', false)->count(),
            'avec_activites' => (clone $summaryQuery)->whereHas('activites')->count(),
        ];

        $extrants = $query->ordered()->paginate(15)->withQueryString();

        $filters = $request->only(['search', 'objectif_id', 'is_active']);

        $objectifs = Objectif::query()
            ->with('exercices:id,annee')
            ->where('statut', 'actif')
            ->forExercice($exerciceId)
            ->when($this->isChefDepartement($user), fn ($q) => $q->whereHas('extrants.activites', fn (Builder $builder) => $builder->where('departement_id', $user->departement_id)))
            ->orderBy('annee', 'desc')
            ->get();

        return view('pages.extrants.index', compact('extrants', 'objectifs', 'summary', 'filters'));
    }

    /**
     * Formulaire de création
     */
    public function create(Request $request)
    {
        $resultats = $this->resultatsSelectionnables();
        $selectedResultat = $request->get('resultat_id');

        return view('pages.extrants.create', compact('resultats', 'selectedResultat'));
    }

    /**
     * Enregistrement
     */
    public function store(Request $request)
    {
        $validated = $request->validate($this->reglesExtrant($request));

        // L'objectif est dérivé du résultat sélectionné (cohérence Objectif → Résultat → Extrant).
        $validated['objectif_id'] = Resultat::whereKey($validated['resultat_id'])->value('objectif_id');

        $extrant = Extrant::create($validated);

        return redirect()->route('extrants.index')
            ->with('success', "Extrant {$extrant->code} créé avec succès.");
    }

    /**
     * Détail d'un extrant
     */
    public function show(Extrant $extrant)
    {
        $user = Auth::user();

        if ($this->isChefDepartement($user) && ! $this->extrantHasDepartmentActivities($extrant, (int) $user->departement_id)) {
            abort(403);
        }

        $extrant->load(['objectif', 'activites' => function ($query) use ($user) {
            if ($this->isChefDepartement($user) && $user?->departement_id) {
                $query->where('departement_id', $user->departement_id);
            }

            $query->with('departement');
            $query->latest()->limit(10);
        }]);

        $stats = [
            'nb_activites' => $extrant->activites()->count(),
            'budget_total' => $extrant->activites()->sum('cout'),
            'budget_moyen' => $extrant->activites()->avg('cout') ?? 0,
            'nb_departements' => $extrant->activites()->distinct('departement_id')->count('departement_id'),
        ];

        $activitesParStatut = $extrant->activites()
            ->selectRaw('statut, COUNT(*) as total')
            ->groupBy('statut')
            ->pluck('total', 'statut')
            ->toArray();

        return view('pages.extrants.show', compact('extrant', 'stats', 'activitesParStatut'));
    }

    /**
     * Formulaire d'édition
     */
    public function edit(Extrant $extrant)
    {
        $resultats = $this->resultatsSelectionnables();

        return view('pages.extrants.edit', compact('extrant', 'resultats'));
    }

    /**
     * Résultats actifs sélectionnables comme parent d'un extrant, limités à l'exercice actif,
     * avec leur objectif de rattachement chargé pour l'affichage.
     */
    private function resultatsSelectionnables()
    {
        $exerciceId = ActiveExercice::id();

        return Resultat::query()
            ->actif()
            ->with(['objectif:id,code,annee,libelle', 'objectif.exercices:id,annee'])
            ->when($exerciceId !== null, fn ($q) => $q->whereHas('objectif.exercices', fn ($o) => $o->where('exercices.id', $exerciceId)))
            ->ordered()
            ->get();
    }

    /**
     * Mise à jour
     */
    public function update(Request $request, Extrant $extrant)
    {
        $validated = $request->validate($this->reglesExtrant($request, $extrant));

        // L'objectif suit le résultat sélectionné.
        $validated['objectif_id'] = Resultat::whereKey($validated['resultat_id'])->value('objectif_id');

        $extrant->update($validated);

        return redirect()->route('extrants.index')
            ->with('success', "Extrant {$extrant->code} mis à jour.");
    }

    /**
     * Règles de saisie d'un extrant.
     *
     * Le code n'est pas unique dans toute la base : la même nomenclature
     * (« EXT_001 », « Extrant 1.1 ») est réutilisée d'un exercice à l'autre.
     * L'unicité s'apprécie **par exercice**, en remontant Extrant → Résultat →
     * Objectif → exercices couverts.
     *
     * @return array<string, mixed>
     */
    private function reglesExtrant(Request $request, ?Extrant $extrant = null): array
    {
        return [
            'resultat_id' => 'required|exists:resultats,id',
            'code' => [
                'required', 'string', 'max:20',
                function (string $attribute, mixed $value, callable $fail) use ($request, $extrant) {
                    $conflit = $this->codeDejaUtiliseDansExercice(
                        (string) $value,
                        (int) $request->input('resultat_id'),
                        $extrant?->id
                    );

                    if ($conflit !== null) {
                        $fail("Le code « {$value} » est déjà utilisé par un extrant de l'exercice {$conflit}.");
                    }
                },
            ],
            'libelle' => 'required|string|max:500',
            'description' => 'nullable|string',
            'ordre' => 'nullable|integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Année du premier exercice où le code est déjà porté par un autre extrant,
     * ou null si le code est libre.
     */
    private function codeDejaUtiliseDansExercice(string $code, int $resultatId, ?int $extrantIdIgnore): ?int
    {
        $objectifId = Resultat::whereKey($resultatId)->value('objectif_id');

        if ($objectifId === null) {
            return null;
        }

        $exerciceIds = Objectif::whereKey($objectifId)
            ->firstOrFail()
            ->exercices()
            ->pluck('exercices.id');

        if ($exerciceIds->isEmpty()) {
            return null;
        }

        // Un extrant en conflit est un extrant de même code dont l'objectif couvre
        // au moins un des exercices visés.
        $conflit = Extrant::query()
            ->where('code', $code)
            ->when($extrantIdIgnore !== null, fn ($q) => $q->whereKeyNot($extrantIdIgnore))
            ->whereHas('objectif.exercices', fn ($q) => $q->whereIn('exercices.id', $exerciceIds))
            ->first();

        if ($conflit === null) {
            return null;
        }

        return (int) $conflit->objectif?->exercices()
            ->whereIn('exercices.id', $exerciceIds)
            ->min('annee');
    }

    /**
     * Suppression
     */
    public function destroy(Extrant $extrant)
    {
        if ($extrant->activites()->count() > 0) {
            return redirect()->route('extrants.index')
                ->with('error', 'Impossible de supprimer un extrant qui a des activités.');
        }

        $code = $extrant->code;
        Extrant::query()->whereKey($extrant->id)->delete();

        return redirect()->route('extrants.index')
            ->with('success', "Extrant {$code} supprimé.");
    }

    /**
     * Activer/Désactiver
     */
    public function toggleStatus(Extrant $extrant)
    {
        $extrant->update(['is_active' => ! $extrant->is_active]);

        $status = $extrant->is_active ? 'activé' : 'désactivé';

        return redirect()->route('extrants.index')
            ->with('success', "Extrant {$extrant->code} {$status}.");
    }

    private function isChefDepartement($user): bool
    {
        return $user?->hasRole('chef') && $user?->departement_id !== null;
    }

    private function extrantHasDepartmentActivities(Extrant $extrant, int $departementId): bool
    {
        return $extrant->activites()
            ->where('departement_id', $departementId)
            ->exists();
    }

    private function applyDepartmentScopeToExtrantQuery(Builder $query, int $departementId): void
    {
        $query->whereHas('activites', fn (Builder $builder) => $builder->where('departement_id', $departementId));
    }
}
