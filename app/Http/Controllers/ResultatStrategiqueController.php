<?php

namespace App\Http\Controllers;

use App\Models\ResultatStrategique;
use App\Models\ObjectifStrategique;
use Illuminate\Http\Request;

class ResultatStrategiqueController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $resultats = ResultatStrategique::with('objectifStrategique')->orderBy('ordre')->paginate(15);
        return view('pages.resultats.index', compact('resultats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $objectifs = ObjectifStrategique::active()->orderBy('ordre')->get();
        return view('pages.resultats.create', compact('objectifs'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'objectif_strategique_id' => 'required|exists:objectifs_strategiques,id',
            'code' => 'required|string|max:20|unique:resultats_strategiques',
            'libelle' => 'required|string',
            'description' => 'nullable|string',
            'ordre' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        ResultatStrategique::create($validated);

        return redirect()->route('resultats.index')
            ->with('success', 'Résultat stratégique créé avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $resultat = ResultatStrategique::with('objectifStrategique', 'extrants')->findOrFail($id);
        return view('pages.resultats.show', compact('resultat'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $resultat = ResultatStrategique::findOrFail($id);
        $objectifs = ObjectifStrategique::active()->orderBy('ordre')->get();
        return view('pages.resultats.edit', compact('resultat', 'objectifs'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $resultat = ResultatStrategique::findOrFail($id);

        $validated = $request->validate([
            'objectif_strategique_id' => 'required|exists:objectifs_strategiques,id',
            'code' => 'required|string|max:20|unique:resultats_strategiques,code,' . $resultat->id,
            'libelle' => 'required|string',
            'description' => 'nullable|string',
            'ordre' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $resultat->update($validated);

        return redirect()->route('resultats.index')
            ->with('success', 'Résultat stratégique mis à jour.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $resultat = ResultatStrategique::findOrFail($id);

        if ($resultat->extrants()->count() > 0) {
            return redirect()->route('resultats.index')
                ->with('error', 'Impossible de supprimer un résultat qui a des extrants.');
        }

        $resultat->delete();
        return redirect()->route('resultats.index')
            ->with('success', 'Résultat stratégique supprimé.');
    }
}
