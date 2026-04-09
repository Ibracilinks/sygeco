<?php

namespace App\Http\Controllers;

use App\Models\Activite;
use App\Models\Extrant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ActiviteController extends Controller
{
    /**
     * Liste des activités
     */
    public function index(Request $request)
    {
        $query = Activite::with('extrant');

        // Filtre par recherche
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('libelle', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filtre par statut
        if ($request->filled('statut')) {
            $query->where('is_active', $request->statut == '1');
        }

        // Filtre par extrant
        if ($request->filled('extrant_id')) {
            $query->where('extrant_id', $request->extrant_id);
        }

        $activites = $query->orderBy('ordre')->orderBy('created_at', 'desc')->paginate(15);
        $extrants = Extrant::where('is_active', true)->get();

        return view('pages.activites.index', compact('activites', 'extrants'));
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        $extrants = Extrant::where('is_active', true)->orderBy('code')->get();
        return view('pages.activites.create', compact('extrants'));
    }

    /**
     * Enregistrer une activité
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'extrant_id' => 'required|exists:extrants,id',
            'code' => 'required|string|max:50|unique:activites,code',
            'libelle' => 'required|string|max:255',
            'description' => 'nullable|string',
            'budget_previsionnel_global' => 'required|numeric|min:0',
            'date_debut_prevue' => 'nullable|date',
            'date_fin_prevue' => 'nullable|date|after_or_equal:date_debut_prevue',
            'ordre' => 'nullable|integer|min:0',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $activite = Activite::create([
                'extrant_id' => $request->extrant_id,
                'code' => $request->code,
                'libelle' => $request->libelle,
                'description' => $request->description,
                'budget_previsionnel_global' => $request->budget_previsionnel_global,
                'date_debut_prevue' => $request->date_debut_prevue,
                'date_fin_prevue' => $request->date_fin_prevue,
                'ordre' => $request->ordre ?? 0,
                'is_active' => $request->has('is_active'),
            ]);

            return redirect()->route('activites.show', $activite)
                ->with('success', 'Activité créée avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de la création: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Afficher une activité
     */
    public function show(Activite $activite)
    {
        $activite->load('extrant');
        return view('pages.activites.show', compact('activite'));
    }

    /**
     * Formulaire d'édition
     */
    public function edit(Activite $activite)
    {
        $extrants = Extrant::where('is_active', true)->orderBy('code')->get();
        return view('pages.activites.edit', compact('activite', 'extrants'));
    }

    /**
     * Mettre à jour une activité
     */
    public function update(Request $request, Activite $activite)
    {
        $validator = Validator::make($request->all(), [
            'extrant_id' => 'required|exists:extrants,id',
            'code' => 'required|string|max:50|unique:activites,code,' . $activite->id,
            'libelle' => 'required|string|max:255',
            'description' => 'nullable|string',
            'budget_previsionnel_global' => 'required|numeric|min:0',
            'date_debut_prevue' => 'nullable|date',
            'date_fin_prevue' => 'nullable|date|after_or_equal:date_debut_prevue',
            'ordre' => 'nullable|integer|min:0',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $activite->update([
                'extrant_id' => $request->extrant_id,
                'code' => $request->code,
                'libelle' => $request->libelle,
                'description' => $request->description,
                'budget_previsionnel_global' => $request->budget_previsionnel_global,
                'date_debut_prevue' => $request->date_debut_prevue,
                'date_fin_prevue' => $request->date_fin_prevue,
                'ordre' => $request->ordre ?? 0,
                'is_active' => $request->has('is_active'),
            ]);

            return redirect()->route('activites.show', $activite)
                ->with('success', 'Activité mise à jour avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de la mise à jour: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Supprimer une activité
     */
    public function destroy(Activite $activite)
    {
        try {
            $activite->delete();
            return redirect()->route('activites.index')
                ->with('success', 'Activité supprimée avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de la suppression: ' . $e->getMessage());
        }
    }

    /**
     * Activer/Désactiver une activité
     */
    public function toggleStatus(Activite $activite)
    {
        $activite->is_active = !$activite->is_active;
        $activite->save();

        $status = $activite->is_active ? 'activée' : 'désactivée';
        return redirect()->back()->with('success', "Activité {$status} avec succès.");
    }

    /**
     * Dupliquer une activité
     */
    public function duplicate(Activite $activite)
    {
        $newActivite = $activite->replicate();
        $newActivite->code = $activite->code . '_COPY';
        $newActivite->is_active = false;
        $newActivite->save();

        return redirect()->route('activites.edit', $newActivite)
            ->with('success', 'Activité dupliquée avec succès. Veuillez modifier le code.');
    }

    /**
     * Exporter les activités en CSV
     */
    public function export(Request $request)
    {
        $query = Activite::with('extrant');

        if ($request->filled('extrant_id')) {
            $query->where('extrant_id', $request->extrant_id);
        }

        $activites = $query->orderBy('ordre')->get();

        $filename = 'activites_' . date('Y-m-d_His') . '.csv';
        $handle = fopen('php://temp', 'w+');

        // En-têtes CSV
        fputcsv($handle, ['Code', 'Libellé', 'Extrant', 'Budget (FCFA)', 'Date début', 'Date fin', 'Ordre', 'Statut']);

        // Données
        foreach ($activites as $activite) {
            fputcsv($handle, [
                $activite->code,
                $activite->libelle,
                $activite->extrant->code ?? '-',
                number_format($activite->budget_previsionnel_global, 0, ',', ' '),
                $activite->date_debut_prevue ? date('d/m/Y', strtotime($activite->date_debut_prevue)) : '-',
                $activite->date_fin_prevue ? date('d/m/Y', strtotime($activite->date_fin_prevue)) : '-',
                $activite->ordre,
                $activite->is_active ? 'Actif' : 'Inactif',
            ]);
        }

        rewind($handle);
        $csvContent = stream_get_contents($handle);
        fclose($handle);

        return response($csvContent, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Tableau de bord des activités
     */
    public function dashboard()
    {
        $stats = [
            'total' => Activite::count(),
            'actives' => Activite::where('is_active', true)->count(),
            'inactives' => Activite::where('is_active', false)->count(),
            'budget_total' => Activite::sum('budget_previsionnel_global'),
        ];

        $activitesParExtrant = Activite::select('extrant_id', DB::raw('count(*) as total'))
            ->groupBy('extrant_id')
            ->with('extrant')
            ->get();

        $recentActivites = Activite::with('extrant')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('pages.activites.dashboard', compact('stats', 'activitesParExtrant', 'recentActivites'));
    }
}
