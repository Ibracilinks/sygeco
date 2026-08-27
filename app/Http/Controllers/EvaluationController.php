<?php

namespace App\Http\Controllers;

use App\Models\Activite;
use App\Models\ActiviteEvaluation;
use App\Models\Departement;
use App\Models\Extrant;
use App\Support\ActiveExercice;
use App\Support\CadreLogique;
use App\Support\VisibiliteActivites;
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
            ->when($exerciceId !== null, fn ($q) => $q->whereHas('objectif.exercices', fn ($oq) => $oq->where('exercices.id', $exerciceId)))
            ->ordered()
            ->get();

        $departements = $this->departementsVisibles();
        $departementsGroupes = Departement::grouperParDirectionCentrale($departements);
        $filters = $request->only(['search', 'extrant_id', 'departement_id', 'statut_execution']);

        $exercice = ActiveExercice::model();
        [$debutFenetre, $finFenetre] = $exercice
            ? $exercice->fenetreEvaluation($periode)
            : [null, null];

        // La saisie mi-parcours et fin d'année est ouverte en permanence : les dates
        // de l'exercice ne sont plus qu'indicatives (bandeau d'information), elles ne
        // ferment plus le formulaire. Seule la permission décide.
        $peutSaisir = true;

        // Chaque période a sa propre page, strictement cloisonnée : mi-parcours et
        // fin d'année ne montrent jamais les données de l'autre période.
        $peutSaisirBudget = $this->peutSaisirBudget();

        return view('pages.evaluations.'.ActiviteEvaluation::slugDePeriode($periode), compact(
            'activites', 'extrants', 'departements', 'departementsGroupes', 'summary', 'filters',
            'exercice', 'periode', 'peutSaisir', 'peutSaisirBudget', 'debutFenetre', 'finFenetre'
        ));
    }

    /**
     * Seule l'administration (superadmin / DBCGOQ) renseigne le budget consommé lors
     * de l'évaluation : les chefs et agents saisissent l'état d'exécution, l'observation
     * et la valeur d'indicateur, pas le montant utilisé.
     */
    private function peutSaisirBudget(): bool
    {
        return Auth::user()?->hasAnyRole(['superadmin', 'dbcgoq']) ?? false;
    }

    /**
     * Enregistrement de l'évaluation d'une activité pour une période.
     */
    public function enregistrer(Request $request, Activite $activite, string $periode)
    {
        $periode = $this->resoudrePeriode($periode);

        $user = Auth::user();

        if (! $user->can('evaluate_activites') && ! $user->can('validate_activites')) {
            return $this->refus($request, "Vous n'êtes pas autorisé à renseigner l'évaluation.");
        }

        if (! $user->can('view', $activite)) {
            abort(403);
        }

        if ($activite->statut !== 'valide') {
            return $this->refus($request, 'Seules les activités validées peuvent être évaluées.');
        }

        // Les fenêtres mi-parcours / fin d'année ne bloquent plus la saisie : elles
        // restent ouvertes en permanence et ne servent qu'à informer l'utilisateur.

        // Une évaluation partielle n'a pas de valeur : tous les champs présentés à
        // l'utilisateur sont exigés. Le budget consommé n'apparaît que pour
        // l'administration, il n'est donc obligatoire que pour elle.
        $validated = $request->validate([
            'statut_execution' => ['required', 'in:non_realise,en_cours,realise'],
            'observation' => ['required', 'string', 'max:1000'],
            'valeur_indicateur' => ['required', 'numeric', 'max:'.Activite::MONTANT_MAX],
        ], [
            'observation.required' => "L'observation est obligatoire.",
            'valeur_indicateur.required' => "La valeur de l'indicateur est obligatoire.",
        ]);

        // Le budget consommé ne se saisit plus depuis l'évaluation : une valeur postée
        // est ignorée, sans écraser le montant déjà enregistré.
        unset($validated['montant_utilise']);

        $activite->evaluations()->updateOrCreate(
            ['periode' => $periode],
            $validated + ['maj_par' => Auth::id(), 'maj_le' => now()]
        );

        // Les tableaux de bord s'appuient sur les colonnes de synthèse de l'activité :
        // on y reporte la dernière évaluation saisie.
        $activite->update([
            'statut_execution' => $validated['statut_execution'],
            'execution_commentaire' => $validated['observation'] ?? null,
            'valeur_indicateur' => $validated['valeur_indicateur'] ?? null,
            'execution_maj_le' => now(),
            'execution_maj_par' => Auth::id(),
        ]);

        $message = 'Évaluation '.ActiviteEvaluation::PERIODES[$periode].' enregistrée.';

        // Saisie depuis la fiche : on renvoie de quoi rafraîchir la ligne sur place,
        // plutôt que de recharger toute la page d'évaluation.
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'ligne' => $this->ligneRafraichie($activite->fresh(), $periode),
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Refus exprimé dans le format attendu par l'appelant.
     */
    private function refus(Request $request, string $message)
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 422);
        }

        return back()->with('error', $message);
    }

    /**
     * Valeurs d'affichage de la ligne après enregistrement. Le badge est rendu côté
     * serveur : dupliquer ses classes en JavaScript les ferait diverger au premier
     * changement de charte.
     *
     * @return array<string, string>
     */
    private function ligneRafraichie(Activite $activite, string $periode): array
    {
        $evaluation = $activite->evaluations()->where('periode', $periode)->with('majPar')->first();
        $montant = $evaluation?->montant_utilise;
        $ecart = $montant !== null ? (float) $activite->cout - (float) $montant : null;

        $nombre = fn ($valeur) => number_format((float) $valeur, 0, ',', ' ');

        return [
            'badge' => view('components.execution-badge', ['statut' => $evaluation?->statut_execution])->render(),
            'maj' => $evaluation?->maj_le
                ? $evaluation->maj_le->format('d/m/Y H:i').($evaluation->majPar ? '<br>par '.e($evaluation->majPar->name) : '')
                : '—',
            'observation' => e($evaluation?->observation ?: '—'),
            'montant' => $montant !== null ? $nombre($montant).' FCFA' : '—',
            'ecart' => $ecart !== null
                ? '('.($ecart < 0 ? 'dépassement' : 'écart').' '.$nombre(abs($ecart)).')'
                : '',
            'ecart_depassement' => $ecart !== null && $ecart < 0,
            'valeur_indicateur' => $evaluation?->valeur_indicateur !== null
                ? rtrim(rtrim(number_format($evaluation->valeur_indicateur, 2, ',', ' '), '0'), ',')
                : '—',
        ];
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

        // Mi-parcours = premier semestre : seules les activités programmées sur T1 ou T2
        // sont évaluables à cette échéance. La fin d'année couvre tout le chronogramme.
        if ($periode === ActiviteEvaluation::PERIODE_MI_PARCOURS) {
            $query->where(fn ($q) => $q->where('trimestre_1', 'oui')->orWhere('trimestre_2', 'oui'));
        }

        // Même visibilité que la programmation.
        VisibiliteActivites::appliquer($query, Auth::user());

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
        // La Direction Générale ne formule pas d'activités : elle reçoit celles des
        // entités qui lui sont rattachées.
        $query = Departement::active()->formulatrices()->ordered();

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
