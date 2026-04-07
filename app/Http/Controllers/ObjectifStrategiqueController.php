<?php

namespace App\Http\Controllers;

use App\Models\ObjectifStrategique;
use Illuminate\Http\Request;

class ObjectifStrategiqueController extends Controller
{
    public function index()
    {
        $objectifs = ObjectifStrategique::orderBy('ordre')->paginate(15);
        return view('pages.objectifs.index', compact('objectifs'));
    }

    public function create()
    {
        return view('pages.objectifs.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:objectifs_strategiques',
            'libelle' => 'required|string',
            'description' => 'nullable|string',
            'ordre' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        ObjectifStrategique::create($validated);

        return redirect()->route('objectifs.index')
            ->with('success', 'Objectif stratégique créé avec succès.');
    }

    public function show(ObjectifStrategique $objectif)
    {
        $objectif->load('resultatsStrategiques');
        return view('pages.objectifs.show', compact('objectif'));
    }

    public function edit(ObjectifStrategique $objectif)
    {
        return view('pages.objectifs.edit', compact('objectif'));
    }

    public function update(Request $request, ObjectifStrategique $objectif)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:objectifs_strategiques,code,' . $objectif->id,
            'libelle' => 'required|string',
            'description' => 'nullable|string',
            'ordre' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $objectif->update($validated);

        return redirect()->route('objectifs.index')
            ->with('success', 'Objectif stratégique mis à jour.');
    }

    public function destroy(ObjectifStrategique $objectif)
    {
        if ($objectif->resultatsStrategiques()->count() > 0) {
            return redirect()->route('objectifs.index')
                ->with('error', 'Impossible de supprimer un objectif qui a des résultats.');
        }

        $objectif->delete();
        return redirect()->route('objectifs.index')
            ->with('success', 'Objectif stratégique supprimé.');
    }
}
