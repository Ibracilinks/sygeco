<?php

namespace App\Http\Controllers;

use App\Models\Extrant;
use App\Models\ResultatStrategique;
use Illuminate\Http\Request;

class ExtrantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $extrants = Extrant::with('resultatStrategique')->orderBy('ordre')->paginate(15);
        return view('pages.extrants.index', compact('extrants'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $resultats = ResultatStrategique::active()->orderBy('ordre')->get();
        return view('pages.extrants.create', compact('resultats'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'resultat_strategique_id' => 'required|exists:resultats_strategiques,id',
            'code' => 'required|string|max:20|unique:extrants',
            'libelle' => 'required|string',
            'description' => 'nullable|string',
            'ordre' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        Extrant::create($validated);

        return redirect()->route('extrants.index')
            ->with('success', 'Extrant créé avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $extrant = Extrant::with('resultatStrategique', 'activites')->findOrFail($id);
        return view('pages.extrants.show', compact('extrant'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $extrant = Extrant::findOrFail($id);
        $resultats = ResultatStrategique::active()->orderBy('ordre')->get();
        return view('pages.extrants.edit', compact('extrant', 'resultats'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $extrant = Extrant::findOrFail($id);

        $validated = $request->validate([
            'resultat_strategique_id' => 'required|exists:resultats_strategiques,id',
            'code' => 'required|string|max:20|unique:extrants,code,' . $extrant->id,
            'libelle' => 'required|string',
            'description' => 'nullable|string',
            'ordre' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $extrant->update($validated);

        return redirect()->route('extrants.index')
            ->with('success', 'Extrant mis à jour.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $extrant = Extrant::findOrFail($id);

        if ($extrant->activites()->count() > 0) {
            return redirect()->route('extrants.index')
                ->with('error', 'Impossible de supprimer un extrant qui a des activités.');
        }

        $extrant->delete();
        return redirect()->route('extrants.index')
            ->with('success', 'Extrant supprimé.');
    }
}
