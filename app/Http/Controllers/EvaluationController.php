<?php

namespace App\Http\Controllers;

use App\Models\Activite;
use App\Models\ActiviteEvaluation;
use App\Models\Departement;
use App\Models\Extrant;
use App\Support\ActiveExercice;
use App\Support\CadreLogique;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EvaluationController extends Controller
{
    /**
     * Évaluation des activités pour une période : mi-parcours ou fin d'année.
     */
    public function index(Request $request, string $periode)
    {
        $periode = $this->resoudrePeriode($periode);

        $query = $this->evaluationQuery($request, $periode)
            ->with(['extrant', 'departement', 'evaluations.majPar']);

        $base = (clone $query);
        $summary = [
            'total' => (clone $base)->count(),
            'non_realise' => (clone $base)->parStatutEvaluation($periode, 'non_realise')->count(),
            'en_cours' => (clone $base)->parStatutEvaluation($periode, 'en_cours')->count(),
            'realise' => (clone $base)->parStatutEvaluation($periode, 'realise')->count(),
            'non_evaluee' => (clone $base)->sansEvaluation($periode)->count(),
        ];
        $summary['evaluees'] = $summary['total'] - $summary['non_evaluee'];

        // Taux de réalisation calculé sur les activités effectivement évaluées :
        // celles qui n'ont pas encore été renseignées ne comptent pas comme non réalisées.
        $summary['taux_realisation'] = $summary['evaluees'] > 0
            ? round($summary['realise'] / $summary['evaluees'] * 100, 1)
            : 0.0;
        $summary['taux_saisie'] = $summary['total'] > 0
            ? round($summary['evaluees'] / $summary['total'] * 100, 1)
            : 0.0;

        $activites = $query->orderBy('extrant_id')->orderBy('id')->paginate(20)->withQueryString();

        $exerciceId = ActiveExercice::id();
        $extrants = Extrant::query()
            ->with('objectif')
            ->actif()
            ->when($exerciceId !== null, fn ($q) => $q->whereHas('objectif', fn ($oq) => $oq->where('exercice_id', $exerciceId)))
            ->ordered()
            ->get();

        $departements = $this->departementsVisibles();
        $departementsGroupes = Departement::grouperParDirectionCentrale($departements);
        $filters = $request->only(['search', 'extrant_id', 'departement_id', 'statut_execution']);

        $exercice = ActiveExercice::model();
        [$debutFenetre, $finFenetre] = $exercice
            ? $exercice->fenetreEvaluation($periode)
            : [null, null];

        // Fenêtre de saisie : ouverte en permanence pour le dbcgoq / superadmin,
        // sinon uniquement pendant la fenêtre de la période concernée.
        $peutSaisir = Auth::user()->can('validate_activites')
            || ($exercice?->enPeriodeEvaluationPour($periode) ?? false);

        // L'autre période, en lecture seule, pour comparaison.
        $autrePeriode = $periode === ActiviteEvaluation::PERIODE_MI_PARCOURS
            ? ActiviteEvaluation::PERIODE_FIN_ANNEE
            : ActiviteEvaluation::PERIODE_MI_PARCOURS;

        return view('pages.evaluations.index', compact(
            'activites', 'extrants', 'departements', 'departementsGroupes', 'summary', 'filters',
            'exercice', 'periode', 'autrePeriode', 'peutSaisir', 'debutFenetre', 'finFenetre'
        ));
    }

    /**
     * Enregistrement de l'évaluation d'une activité pour une période.
     */
    public function enregistrer(Request $request, Activite $activite, string $periode)
    {
        $periode = $this->resoudrePeriode($periode);

        $user = Auth::user();

        if (! $user->can('edit_activites') && ! $user->can('validate_activites')) {
            return back()->with('error', "Vous n'êtes pas autorisé à renseigner l'évaluation.");
        }

        if (! $user->can('view', $activite)) {
            abort(403);
        }

        if ($activite->statut !== 'valide') {
            return back()->with('error', "Seules les activités validées peuvent être évaluées.");
        }

        // La fenêtre s'apprécie sur l'exercice de l'activité, pas sur celui que
        // l'utilisateur a sélectionné dans son contexte de navigation.
        $exercice = $activite->exercice();
        $peutSaisir = $user->can('validate_activites')
            || ($exercice?->enPeriodeEvaluationPour($periode) ?? false);

        if (! $peutSaisir) {
            return back()->with('error', 'La fenêtre de saisie '.ActiviteEvaluation::PERIODES[$periode].' est fermée.');
        }

        $validated = $request->validate([
            'statut_execution' => ['required', 'in:non_realise,en_cours,realise'],
            'observation' => ['nullable', 'string', 'max:1000'],
            'montant_utilise' => ['nullable', 'numeric', 'min:0', 'max:'.Activite::MONTANT_MAX],
            'valeur_indicateur' => ['nullable', 'numeric', 'max:'.Activite::MONTANT_MAX],
        ]);

        $activite->evaluations()->updateOrCreate(
            ['periode' => $periode],
            $validated + ['maj_par' => Auth::id(), 'maj_le' => now()]
        );

        // Les tableaux de bord s'appuient sur les colonnes de synthèse de l'activité :
        // on y reporte la dernière évaluation saisie.
        $activite->update([
            'statut_execution' => $validated['statut_execution'],
            'execution_commentaire' => $validated['observation'] ?? null,
            'montant_utilise' => $validated['montant_utilise'] ?? null,
            'valeur_indicateur' => $validated['valeur_indicateur'] ?? null,
            'execution_maj_le' => now(),
            'execution_maj_par' => Auth::id(),
        ]);

        return back()->with('success', 'Évaluation '.ActiviteEvaluation::PERIODES[$periode].' enregistrée.');
    }

    /**
     * Export du cadre logique d'évaluation pour la période affichée.
     */
    public function exporter(Request $request, string $periode)
    {
        $periode = $this->resoudrePeriode($periode);

        $activites = $this->evaluationQuery($request, $periode)
            ->with([
                'extrant.resultat.objectif',
                'departement:id,code,nom',
                'departements:id,code,nom',
                'evaluations',
            ])
            ->get();

        $objectifs = CadreLogique::grouper($activites);
        $periodeLibelle = ActiviteEvaluation::PERIODES[$periode];
        $entetePeriode = $periode === ActiviteEvaluation::PERIODE_MI_PARCOURS ? '1er SEMESTRE' : 'ANNÉE';

        $html = view('pages.evaluations.export', compact('objectifs', 'periode', 'periodeLibelle', 'entetePeriode'))->render();
        $filename = 'cadre-logique-evaluation-'.ActiviteEvaluation::slugDePeriode($periode).'-'.now()->format('Ymd_His').'.xls';

        return response($html, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    /**
     * Requête commune : activités de l'exercice en cours dans le périmètre de l'utilisateur.
     */
    private function evaluationQuery(Request $request, string $periode)
    {
        // Seules les activités validées sont évaluées : la programmation en brouillon,
        // en attente d'arbitrage ou rejetée n'entre pas dans l'évaluation.
        $query = Activite::query()->forExercice(ActiveExercice::id())->valide();

        // Même périmètre que la programmation : sous-arbre pour les chefs, entité propre pour les agents.
        if ($perimetre = Auth::user()?->perimetreActivitesIds()) {
            $query->whereIn('departement_id', $perimetre);
        }

        if ($request->filled('extrant_id')) {
            $query->where('extrant_id', $request->extrant_id);
        }
        if ($request->filled('departement_id')) {
            $query->where('departement_id', $request->departement_id);
        }
        if ($request->filled('statut_execution')) {
            $request->statut_execution === 'non_evaluee'
                ? $query->sansEvaluation($periode)
                : $query->parStatutEvaluation($periode, $request->statut_execution);
        }
        if ($request->filled('search')) {
            $query->where('nom_activite', 'like', "%{$request->search}%");
        }

        return $query;
    }

    private function departementsVisibles()
    {
        $query = Departement::active()->ordered();

        if ($perimetre = Auth::user()?->perimetreActivitesIds()) {
            $query->whereIn('id', $perimetre);
        }

        return $query->get();
    }

    /**
     * Traduit le segment d'URL (mi-parcours / fin-annee) en période stockée.
     */
    private function resoudrePeriode(string $slug): string
    {
        return ActiviteEvaluation::periodeDepuisSlug($slug) ?? abort(404);
    }
}
