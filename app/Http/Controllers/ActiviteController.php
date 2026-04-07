<?php

namespace App\Http\Controllers;

use App\Models\Activite;
use App\Models\Extrant;
use Illuminate\Http\Request;

class ActiviteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $activites = Activite::with('extrant')->orderBy('ordre')->paginate(15);
        return view('pages.activites.index', compact('activites'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $extrants = Extrant::active()->orderBy('ordre')->get();
        return view('pages.activites.create', compact('extrants'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'extrant_id' => 'required|exists:extrants,id',
            'code' => 'required|string|max:50|unique:activites',
            'libelle' => 'required|string',
            'description' => 'nullable|string',
            'budget_previsionnel_global' => 'nullable|numeric|min:0',
            'date_debut_prevue' => 'nullable|date',
            'date_fin_prevue' => 'nullable|date|after_or_equal:date_debut_prevue',
            'ordre' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        Activite::create($validated);

        return redirect()->route('activites.index')
            ->with('success', 'Activité créée avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $activite = Activite::with('extrant')->findOrFail($id);
        return view('pages.activites.show', compact('activite'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $activite = Activite::findOrFail($id);
        $extrants = Extrant::active()->orderBy('ordre')->get();
        return view('pages.activites.edit', compact('activite', 'extrants'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $activite = Activite::findOrFail($id);

        $validated = $request->validate([
            'extrant_id' => 'required|exists:extrants,id',
            'code' => 'required|string|max:50|unique:activites,code,' . $activite->id,
            'libelle' => 'required|string',
            'description' => 'nullable|string',
            'budget_previsionnel_global' => 'nullable|numeric|min:0',
            'date_debut_prevue' => 'nullable|date',
            'date_fin_prevue' => 'nullable|date|after_or_equal:date_debut_prevue',
            'ordre' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $activite->update($validated);

        return redirect()->route('activites.index')
            ->with('success', 'Activité mise à jour.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $activite = Activite::findOrFail($id);

        $activite->delete();
        return redirect()->route('activites.index')
            ->with('success', 'Activité supprimée.');
    }
}
