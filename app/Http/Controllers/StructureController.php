<?php

namespace App\Http\Controllers;

use App\Models\Structure;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StructureController extends Controller
{
    public function index()
    {
        $structures = Structure::with('parent')
            ->orderBy('type')
            ->orderBy('libelle')
            ->paginate(15);

        return view('pages.structures.index', compact('structures'));
    }

    public function create()
    {
        $parents = Structure::orderBy('libelle')->get();
        return view('pages.structures.create', compact('parents'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:structures',
            'libelle' => 'required|string|max:200',
            'type' => ['required', Rule::in(['departement', 'bureau_regional', 'direction'])],
            'parent_id' => 'nullable|exists:structures,id',
            'responsable_nom' => 'nullable|string|max:100',
            'responsable_email' => 'nullable|email|max:100',
            'telephone' => 'nullable|string|max:20',
            'adresse' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        Structure::create($validated);

        return redirect()->route('structures.index')
            ->with('success', 'Structure créée avec succès.');
    }

    public function show(Structure $structure)
    {
        $structure->load('parent', 'enfants');
        return view('pages.structures.show', compact('structure'));
    }

    public function edit(Structure $structure)
    {
        $parents = Structure::where('id', '!=', $structure->id)
            ->orderBy('libelle')
            ->get();

        return view('pages.structures.edit', compact('structure', 'parents'));
    }

    public function update(Request $request, Structure $structure)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('structures')->ignore($structure->id)],
            'libelle' => 'required|string|max:200',
            'type' => ['required', Rule::in(['departement', 'bureau_regional', 'direction'])],
            'parent_id' => 'nullable|exists:structures,id',
            'responsable_nom' => 'nullable|string|max:100',
            'responsable_email' => 'nullable|email|max:100',
            'telephone' => 'nullable|string|max:20',
            'adresse' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $structure->update($validated);

        return redirect()->route('structures.index')
            ->with('success', 'Structure mise à jour avec succès.');
    }

    public function destroy(Structure $structure)
    {
        if ($structure->enfants()->count() > 0) {
            return redirect()->route('structures.index')
                ->with('error', 'Impossible de supprimer une structure qui a des sous-structures.');
        }

        $structure->delete();

        return redirect()->route('structures.index')
            ->with('success', 'Structure supprimée avec succès.');
    }
}
