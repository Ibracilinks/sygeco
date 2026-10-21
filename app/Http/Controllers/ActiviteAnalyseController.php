<?php

namespace App\Http\Controllers;

use App\Models\Activite;
use App\Support\ActiveExercice;
use App\Support\AnalyseActivites;
use App\Support\VisibiliteActivites;
use Illuminate\Support\Facades\Auth;

/**
 * Vue d'ensemble de la programmation de l'exercice actif (toutes entités) :
 * indicateurs et doublons potentiels, pour la cellule planification, le contrôle
 * de gestion et la DBCGOQ.
 */
class ActiviteAnalyseController extends Controller
{
    public function index()
    {
        $exercice = ActiveExercice::model();

        $query = Activite::query()
            ->forExercice($exercice?->id)
            ->with(['extrant:id,code,libelle', 'departement:id,code,nom'])
            ->orderBy('nom_activite');
        VisibiliteActivites::appliquer($query, Auth::user());
        $activites = $query->get();

        $analyse = new AnalyseActivites($activites);
        $indicateurs = $analyse->indicateurs();
        $doublons = $analyse->doublons();

        return view('pages.activites.analyse', compact('exercice', 'indicateurs', 'doublons'));
    }
}
