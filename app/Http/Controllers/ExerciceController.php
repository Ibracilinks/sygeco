<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExerciceRequest;
use App\Http\Requests\UpdateExerciceRequest;
use App\Models\Exercice;
use App\Support\ActiveExercice;
use Illuminate\Http\Request;

class ExerciceController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Exercice::class, 'exercice');
    }

    public function index(Request $request)
    {
        $query = Exercice::query()->ordered();

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        $exercices = $query->paginate(15)->withQueryString();

        return view('pages.exercices.index', compact('exercices'));
    }

    public function create()
    {
        return view('pages.exercices.create');
    }

    public function store(StoreExerciceRequest $request)
    {
        $data = $request->validated();
        $this->ensureSingleActif($data['statut'] ?? null);

        Exercice::create($data);

        return redirect()->route('exercices.index')
            ->with('success', 'Exercice créé.');
    }

    public function show(Exercice $exercice)
    {
        $exercice->loadCount('objectifs');

        return view('pages.exercices.show', compact('exercice'));
    }

    public function edit(Exercice $exercice)
    {
        return view('pages.exercices.edit', compact('exercice'));
    }

    public function update(UpdateExerciceRequest $request, Exercice $exercice)
    {
        $data = $request->validated();
        $this->ensureSingleActif($data['statut'] ?? null, $exercice->id);

        $exercice->update($data);

        return redirect()->route('exercices.index')
            ->with('success', 'Exercice mis à jour.');
    }

    public function destroy(Exercice $exercice)
    {
        if ($exercice->objectifs()->exists()) {
            return redirect()->route('exercices.index')
                ->with('error', 'Impossible de supprimer un exercice lié à des objectifs.');
        }

        $exercice->delete();

        if (ActiveExercice::id() === (int) $exercice->id) {
            ActiveExercice::set(null);
        }

        return redirect()->route('exercices.index')
            ->with('success', 'Exercice supprimé.');
    }

    public function activate(Exercice $exercice)
    {
        $this->authorize('view', $exercice);

        ActiveExercice::set((int) $exercice->id);

        return redirect()->back()
            ->with('success', 'Exercice actif mis à jour pour cette session.');
    }

    protected function ensureSingleActif(?string $statut, ?int $exceptId = null): void
    {
        if ($statut !== 'actif') {
            return;
        }

        $q = Exercice::query()->where('statut', 'actif');
        if ($exceptId) {
            $q->where('id', '!=', $exceptId);
        }
        $q->update(['statut' => 'cloture']);
    }
}
