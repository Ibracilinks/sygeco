<?php

namespace App\Http\Controllers;

use App\Models\Extrant;
use App\Models\Objectif;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExtrantController extends Controller
{
    /**
     * Liste des extrants
     */
    public function index(Request $request)
    {
        $query = Extrant::with('objectif');

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

        $extrants = $query->ordered()->paginate(15)->withQueryString();

        $objectifs = Objectif::where('statut', 'actif')->orderBy('annee', 'desc')->get();

        return view('pages.extrants.index', compact('extrants', 'objectifs'));
    }

    /**
     * Formulaire de création
     */
    public function create(Request $request)
    {
        $objectifs = Objectif::where('statut', 'actif')->orderBy('annee', 'desc')->get();
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
        $extrant->load(['objectif', 'activites' => function ($query) {
            $query->latest()->limit(10);
        }]);

        $stats = [
            'nb_activites' => $extrant->activites()->count(),
            'budget_total' => $extrant->activites()->sum('cout'),
            'budget_moyen' => $extrant->activites()->avg('cout') ?? 0,
        ];

        return view('pages.extrants.show', compact('extrant', 'stats'));
    }

    /**
     * Formulaire d'édition
     */
    public function edit(Extrant $extrant)
    {
        $objectifs = Objectif::where('statut', 'actif')->orderBy('annee', 'desc')->get();
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
        $extrant->delete();

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
}
