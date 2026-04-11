<?php

namespace App\Http\Controllers;

use App\Models\Departement;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DepartementController extends Controller
{
    public function index()
    {
        $departements = Departement::ordered()
            ->withCount('users')
            ->paginate(15);

        return view('pages.departements.index', compact('departements'));
    }

    public function create()
    {
        return view('pages.departements.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:departements',
            'nom' => 'required|string|max:200',
            'description' => 'nullable|string',
            'responsable_nom' => 'nullable|string|max:100',
            'responsable_email' => 'nullable|email|max:100',
            'telephone' => 'nullable|string|max:20',
            'is_active' => 'boolean',
            'ordre' => 'nullable|integer',
        ]);

        $validated['ordre'] = $validated['ordre'] ?? Departement::max('ordre') + 1;

        Departement::create($validated);

        return redirect()->route('departements.index')
            ->with('success', 'Département créé avec succès.');
    }

    public function show(Departement $departement)
    {
        $departement->load('users');
        return view('pages.departements.show', compact('departement'));
    }

    public function edit(Departement $departement)
    {
        return view('pages.departements.edit', compact('departement'));
    }

    public function update(Request $request, Departement $departement)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20', Rule::unique('departements')->ignore($departement->id)],
            'nom' => 'required|string|max:200',
            'description' => 'nullable|string',
            'responsable_nom' => 'nullable|string|max:100',
            'responsable_email' => 'nullable|email|max:100',
            'telephone' => 'nullable|string|max:20',
            'is_active' => 'boolean',
            'ordre' => 'nullable|integer',
        ]);

        $departement->update($validated);

        return redirect()->route('departements.index')
            ->with('success', 'Département mis à jour.');
    }

    public function destroy(Departement $departement)
    {
        if ($departement->users()->count() > 0) {
            return redirect()->route('departements.index')
                ->with('error', 'Impossible de supprimer un département qui a des utilisateurs.');
        }

        $departement->delete();

        return redirect()->route('departements.index')
            ->with('success', 'Département supprimé.');
    }
}
