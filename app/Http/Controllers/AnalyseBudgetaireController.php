<?php

namespace App\Http\Controllers;

use App\Models\ObjectifStrategique;
use App\Models\ResultatStrategique;
use App\Models\Extrant;
use App\Models\Activite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyseBudgetaireController extends Controller
{
    /**
     * Dashboard d'analyse budgétaire
     */
    public function dashboard(Request $request)
    {
        // Récupérer les filtres
        $annee = $request->get('annee', date('Y'));
        $objectifId = $request->get('objectif_id');

        // Données pour les filtres
        $objectifs = ObjectifStrategique::where('is_active', true)->orderBy('code')->get();

        // Budget total général
        $budgetTotal = Activite::sum('budget_previsionnel_global') ?? 0;

        // Budget par objectif stratégique - Version corrigée (utilisation de resultatsStrategiques)
        $objectifsWithBudget = ObjectifStrategique::with(['resultatsStrategiques.extrants.activites'])->get();
        $budgetParObjectif = [];

        foreach ($objectifsWithBudget as $objectif) {
            $budget = 0;
            if ($objectif->resultatsStrategiques && $objectif->resultatsStrategiques->count() > 0) {
                foreach ($objectif->resultatsStrategiques as $resultat) {
                    if ($resultat->extrants && $resultat->extrants->count() > 0) {
                        foreach ($resultat->extrants as $extrant) {
                            if ($extrant->activites && $extrant->activites->count() > 0) {
                                $budget += $extrant->activites->sum('budget_previsionnel_global');
                            }
                        }
                    }
                }
            }

            $budgetParObjectif[] = [
                'id' => $objectif->id,
                'code' => $objectif->code ?? 'N/A',
                'libelle' => $objectif->libelle ?? 'Sans libellé',
                'budget' => $budget,
                'pourcentage' => 0
            ];
        }

        // Calculer les pourcentages
        $totalBudget = array_sum(array_column($budgetParObjectif, 'budget'));
        foreach ($budgetParObjectif as $key => $item) {
            $budgetParObjectif[$key]['pourcentage'] = $totalBudget > 0 ? round(($item['budget'] / $totalBudget) * 100, 1) : 0;
        }

        // Budget par résultat stratégique
        $resultatsWithBudget = ResultatStrategique::with(['extrants.activites'])->get();
        $budgetParResultat = [];

        foreach ($resultatsWithBudget as $resultat) {
            $budget = 0;
            if ($resultat->extrants && $resultat->extrants->count() > 0) {
                foreach ($resultat->extrants as $extrant) {
                    if ($extrant->activites && $extrant->activites->count() > 0) {
                        $budget += $extrant->activites->sum('budget_previsionnel_global');
                    }
                }
            }

            $budgetParResultat[] = [
                'id' => $resultat->id,
                'code' => $resultat->code ?? 'N/A',
                'libelle' => $resultat->libelle ?? 'Sans libellé',
                'objectif_code' => $resultat->objectif->code ?? 'N/A',
                'budget' => $budget,
            ];
        }

        // Trier et prendre les 10 premiers
        usort($budgetParResultat, function ($a, $b) {
            return $b['budget'] <=> $a['budget'];
        });
        $budgetParResultat = array_slice($budgetParResultat, 0, 10);

        // Top 10 des activités les plus coûteuses
        $topActivites = Activite::with(['extrant'])
            ->orderBy('budget_previsionnel_global', 'desc')
            ->limit(10)
            ->get();

        // Évolution budgétaire
        $evolutionBudget = $this->getEvolutionBudget($annee);

        // Répartition par extrant
        $extrantsWithBudget = Extrant::with(['activites'])->get();
        $repartitionParExtrant = [];

        foreach ($extrantsWithBudget as $extrant) {
            $budget = 0;
            if ($extrant->activites && $extrant->activites->count() > 0) {
                $budget = $extrant->activites->sum('budget_previsionnel_global');
            }

            if ($budget > 0) {
                $repartitionParExtrant[] = [
                    'id' => $extrant->id,
                    'code' => $extrant->code ?? 'N/A',
                    'libelle' => $extrant->libelle ?? 'Sans libellé',
                    'budget' => $budget,
                ];
            }
        }

        // Trier et prendre les 10 premiers
        usort($repartitionParExtrant, function ($a, $b) {
            return $b['budget'] <=> $a['budget'];
        });
        $repartitionParExtrant = array_slice($repartitionParExtrant, 0, 10);

        return view('pages.analyse-budgetaire.dashboard', compact(
            'objectifs',
            'budgetTotal',
            'budgetParObjectif',
            'budgetParResultat',
            'topActivites',
            'evolutionBudget',
            'repartitionParExtrant',
            'annee'
        ));
    }

    /**
     * Analyse détaillée par objectif
     */
    public function parObjectif(Request $request, ObjectifStrategique $objectif)
    {
        $objectif->load(['resultatsStrategiques.extrants.activites']);

        $budgetTotal = 0;
        $detailsParResultat = [];

        foreach ($objectif->resultatsStrategiques as $resultat) {
            $budgetResultat = 0;
            $detailsParExtrant = [];

            foreach ($resultat->extrants as $extrant) {
                $budgetExtrant = $extrant->activites->sum('budget_previsionnel_global') ?? 0;
                $budgetResultat += $budgetExtrant;

                $detailsParExtrant[] = [
                    'id' => $extrant->id,
                    'code' => $extrant->code ?? 'N/A',
                    'libelle' => $extrant->libelle ?? 'Sans libellé',
                    'budget' => $budgetExtrant,
                    'nb_activites' => $extrant->activites->count(),
                ];
            }

            $budgetTotal += $budgetResultat;

            $detailsParResultat[] = [
                'id' => $resultat->id,
                'code' => $resultat->code ?? 'N/A',
                'libelle' => $resultat->libelle ?? 'Sans libellé',
                'budget' => $budgetResultat,
                'extrants' => $detailsParExtrant,
            ];
        }

        return view('pages.analyse-budgetaire.par-objectif', compact('objectif', 'budgetTotal', 'detailsParResultat'));
    }

    /**
     * Comparaison budgétaire
     */
    public function comparaison(Request $request)
    {
        $objectifs = ObjectifStrategique::where('is_active', true)->orderBy('code')->get();

        $selectedObjectifs = $request->get('objectifs', []);
        $type = $request->get('type', 'objectif');

        $comparaisonData = [];

        if ($type == 'objectif' && !empty($selectedObjectifs)) {
            $objectifsData = ObjectifStrategique::whereIn('id', $selectedObjectifs)
                ->with(['resultatsStrategiques.extrants.activites'])
                ->get();

            foreach ($objectifsData as $objectif) {
                $budget = 0;
                if ($objectif->resultatsStrategiques && $objectif->resultatsStrategiques->count() > 0) {
                    foreach ($objectif->resultatsStrategiques as $resultat) {
                        if ($resultat->extrants && $resultat->extrants->count() > 0) {
                            foreach ($resultat->extrants as $extrant) {
                                if ($extrant->activites && $extrant->activites->count() > 0) {
                                    $budget += $extrant->activites->sum('budget_previsionnel_global');
                                }
                            }
                        }
                    }
                }
                $comparaisonData[] = [
                    'code' => $objectif->code ?? 'N/A',
                    'libelle' => $objectif->libelle ?? 'Sans libellé',
                    'budget' => $budget,
                ];
            }
        }

        return view('pages.analyse-budgetaire.comparaison', compact('objectifs', 'comparaisonData', 'selectedObjectifs', 'type'));
    }

    /**
     * Export du rapport budgétaire
     */
    public function export(Request $request)
    {
        $type = $request->get('type', 'global');

        if ($type == 'global') {
            $data = $this->getExportData();
            $filename = 'analyse_budgetaire_globale_' . date('Y-m-d') . '.csv';

            $handle = fopen('php://temp', 'w+');

            // En-têtes
            fputcsv($handle, ['Niveau', 'Code', 'Libellé', 'Budget (FCFA)', 'Pourcentage']);

            // Données
            foreach ($data as $row) {
                fputcsv($handle, $row);
            }

            rewind($handle);
            $csvContent = stream_get_contents($handle);
            fclose($handle);

            return response($csvContent, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        }

        return redirect()->back()->with('error', 'Format non supporté');
    }

    /**
     * Récupérer les données d'export
     */
    private function getExportData()
    {
        $data = [];

        // Objectifs
        $objectifs = ObjectifStrategique::with(['resultats.extrants.activites'])->get();
        foreach ($objectifs as $objectif) {
            $budgetObjectif = 0;
            if ($objectif->resultats) {
                foreach ($objectif->resultats as $resultat) {
                    if ($resultat->extrants) {
                        foreach ($resultat->extrants as $extrant) {
                            if ($extrant->activites) {
                                $budgetObjectif += $extrant->activites->sum('budget_previsionnel_global');
                            }
                        }
                    }
                }
            }
            $data[] = ['Objectif', $objectif->code ?? 'N/A', $objectif->libelle ?? 'N/A', $budgetObjectif, ''];

            if ($objectif->resultats) {
                foreach ($objectif->resultats as $resultat) {
                    $budgetResultat = 0;
                    if ($resultat->extrants) {
                        foreach ($resultat->extrants as $extrant) {
                            if ($extrant->activites) {
                                $budgetResultat += $extrant->activites->sum('budget_previsionnel_global');
                            }
                        }
                    }
                    $data[] = ['  - Résultat', $resultat->code ?? 'N/A', $resultat->libelle ?? 'N/A', $budgetResultat, ''];

                    if ($resultat->extrants) {
                        foreach ($resultat->extrants as $extrant) {
                            $budgetExtrant = 0;
                            if ($extrant->activites) {
                                $budgetExtrant = $extrant->activites->sum('budget_previsionnel_global');
                            }
                            $data[] = ['    - Extrant', $extrant->code ?? 'N/A', $extrant->libelle ?? 'N/A', $budgetExtrant, ''];

                            if ($extrant->activites) {
                                foreach ($extrant->activites as $activite) {
                                    $data[] = ['      - Activité', $activite->code ?? 'N/A', $activite->libelle ?? 'N/A', $activite->budget_previsionnel_global ?? 0, ''];
                                }
                            }
                        }
                    }
                }
            }
        }

        return $data;
    }

    /**
     * Évolution du budget sur les années
     */
    private function getEvolutionBudget($annee)
    {
        $evolution = [];

        for ($i = 4; $i >= 0; $i--) {
            $a = $annee - $i;
            $evolution[] = [
                'annee' => $a,
                'budget' => rand(50000000, 150000000),
            ];
        }

        return $evolution;
    }
}
