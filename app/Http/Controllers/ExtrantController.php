<?php

namespace App\Http\Controllers;

use App\Models\Extrant;
use App\Models\Objectif;
use App\Support\ActiveExercice;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

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
            $query->whereHas('objectif', fn ($q) => $q->where('exercice_id', $exerciceId));
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
            ->where('statut', 'actif')
            ->when($exerciceId !== null, fn ($q) => $q->where('exercice_id', $exerciceId))
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
        $exerciceId = ActiveExercice::id();
        $objectifs = Objectif::query()
            ->where('statut', 'actif')
            ->when($exerciceId !== null, fn ($q) => $q->where('exercice_id', $exerciceId))
            ->orderBy('annee', 'desc')
            ->get();
        $selectedObjectif = $request->get('objectif_id');

        return view('pages.extrants.create', compact('objectifs', 'selectedObjectif'));
    }

    /**
     * Enregistrement
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'objectif_id' => 'required|exists:objectifs,id',
            'code' => 'required|string|max:20|unique:extrants',
            'libelle' => 'required|string|max:500',
            'description' => 'nullable|string',
            'ordre' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

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
        $exerciceId = ActiveExercice::id();
        $objectifs = Objectif::query()
            ->where('statut', 'actif')
            ->when($exerciceId !== null, fn ($q) => $q->where('exercice_id', $exerciceId))
            ->orderBy('annee', 'desc')
            ->get();

        return view('pages.extrants.edit', compact('extrant', 'objectifs'));
    }

    /**
     * Mise à jour
     */
    public function update(Request $request, Extrant $extrant)
    {
        $validated = $request->validate([
            'objectif_id' => 'required|exists:objectifs,id',
            'code' => ['required', 'string', 'max:20', Rule::unique('extrants')->ignore($extrant->id)],
            'libelle' => 'required|string|max:500',
            'description' => 'nullable|string',
            'ordre' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $extrant->update($validated);

        return redirect()->route('extrants.index')
            ->with('success', "Extrant {$extrant->code} mis à jour.");
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
        $extrant->update(['is_active' => !$extrant->is_active]);

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
