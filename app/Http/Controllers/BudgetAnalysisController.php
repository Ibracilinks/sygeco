<?php

namespace App\Http\Controllers;

use App\Services\DashboardDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BudgetAnalysisController extends Controller
{
    protected $dashboardService;

    public function __construct(DashboardDataService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Display the budget analysis dashboard
     */
    public function index(Request $request)
    {
        $annee = $request->get('annee', now()->year);

        return view('budget-analysis', [
            'annee' => $annee,
            'budgetOverview' => $this->getBudgetOverview($annee),
            'budgetVsActual' => $this->getBudgetVsActual($annee),
            'departmentBudgetAnalysis' => $this->getDepartmentBudgetAnalysis($annee),
            'quarterlyBudgetPlanning' => $this->getQuarterlyBudgetPlanning($annee),
            'budgetAlerts' => $this->getBudgetAlerts($annee),
            'budgetTrends' => $this->getBudgetTrends($annee),
        ]);
    }

    /**
     * Get budget overview data
     */
    private function getBudgetOverview($annee)
    {
        $stats = $this->dashboardService->getStats();

        return [
            'total_budget' => $stats['budget_total'],
            'total_activities' => $stats['total_activites'],
            'average_cost_per_activity' => $stats['total_activites'] > 0 ? $stats['budget_total'] / $stats['total_activites'] : 0,
            'budget_utilization_rate' => $this->calculateBudgetUtilizationRate($annee),
        ];
    }

    /**
     * Get budget vs actual spending analysis
     */
    private function getBudgetVsActual($annee)
    {
        // This would typically compare planned vs actual budgets
        // For now, we'll show current spending patterns
        return [
            'planned_vs_actual' => $this->dashboardService->getBudgetParObjectif(),
            'monthly_spending' => $this->dashboardService->getEvolutionMensuelle(),
            'spending_efficiency' => $this->calculateSpendingEfficiency($annee),
        ];
    }

    /**
     * Get department-wise budget analysis
     */
    private function getDepartmentBudgetAnalysis($annee)
    {
        return [
            'department_spending' => $this->dashboardService->getBudgetParDepartement(),
            'department_efficiency' => $this->calculateDepartmentEfficiency($annee),
            'department_variance' => $this->calculateDepartmentVariance($annee),
        ];
    }

    /**
     * Get quarterly budget planning data
     */
    private function getQuarterlyBudgetPlanning($annee)
    {
        return [
            'quarterly_distribution' => $this->dashboardService->getActivitesParTrimestre(),
            'quarterly_budget_allocation' => $this->calculateQuarterlyBudgetAllocation($annee),
            'quarterly_performance' => $this->calculateQuarterlyPerformance($annee),
        ];
    }

    /**
     * Get budget alerts and warnings
     */
    private function getBudgetAlerts($annee)
    {
        return [
            'over_budget_departments' => $this->getOverBudgetDepartments($annee),
            'under_utilized_budgets' => $this->getUnderUtilizedBudgets($annee),
            'high_cost_activities' => $this->getHighCostActivities($annee),
        ];
    }

    /**
     * Get budget trends over time
     */
    private function getBudgetTrends($annee)
    {
        return [
            'yearly_trends' => $this->calculateYearlyTrends($annee),
            'growth_rate' => $this->calculateBudgetGrowthRate($annee),
            'forecast' => $this->generateBudgetForecast($annee),
        ];
    }

    // Helper methods for calculations

    private function calculateBudgetUtilizationRate($annee)
    {
        // Simplified calculation - in a real app, this would compare against planned budget
        $stats = $this->dashboardService->getStats();
        // Assuming 80% is a typical utilization rate for demonstration
        return 85.5; // This would be calculated based on planned vs actual
    }

    private function calculateSpendingEfficiency($annee)
    {
        // Efficacité des dépenses = part du budget effectivement exécuté :
        // coût des activités « réalisées » rapporté au coût total planifié de l'exercice.
        $row = DB::table('activites')
            ->join('extrants', 'activites.extrant_id', '=', 'extrants.id')
            ->join('objectifs', 'extrants.objectif_id', '=', 'objectifs.id')
            ->where('objectifs.annee', $annee)
            ->selectRaw("
                COALESCE(SUM(activites.cout), 0) as budget_total,
                COALESCE(SUM(CASE WHEN activites.statut_execution = 'realise' THEN activites.cout ELSE 0 END), 0) as budget_realise
            ")
            ->first();

        if (! $row || (float) $row->budget_total <= 0.0) {
            return 0.0;
        }

        return round(((float) $row->budget_realise / (float) $row->budget_total) * 100, 1);
    }

    private function calculateDepartmentEfficiency($annee)
    {
        $departments = $this->dashboardService->getBudgetParDepartement();
        $efficiency = [];

        foreach ($departments as $dept) {
            // Simplified efficiency calculation
            $efficiency[] = [
                'nom' => $dept['nom'],
                'efficiency' => rand(75, 95), // In real app, calculate based on completion rates
                'budget' => $dept['budget'],
            ];
        }

        return $efficiency;
    }

    private function calculateDepartmentVariance($annee)
    {
        $departments = $this->dashboardService->getBudgetParDepartement();
        $variance = [];

        foreach ($departments as $dept) {
            // Calculate variance (planned - actual)
            $planned = $dept['budget'] * 1.1; // Assuming 10% buffer
            $actual = $dept['budget'];
            $variance[] = [
                'nom' => $dept['nom'],
                'planned' => $planned,
                'actual' => $actual,
                'variance' => $planned - $actual,
                'variance_percent' => $planned > 0 ? (($planned - $actual) / $planned) * 100 : 0,
            ];
        }

        return $variance;
    }

    private function calculateQuarterlyBudgetAllocation($annee)
    {
        $quarterly = $this->dashboardService->getActivitesParTrimestre();
        $total = array_sum($quarterly);

        return [
            'Q1' => $total > 0 ? ($quarterly[0] / $total) * 100 : 0,
            'Q2' => $total > 0 ? ($quarterly[1] / $total) * 100 : 0,
            'Q3' => $total > 0 ? ($quarterly[2] / $total) * 100 : 0,
            'Q4' => $total > 0 ? ($quarterly[3] / $total) * 100 : 0,
        ];
    }

    private function calculateQuarterlyPerformance($annee)
    {
        // Simplified quarterly performance calculation
        return [
            'Q1' => ['budget' => 25, 'actual' => 22, 'performance' => 88],
            'Q2' => ['budget' => 30, 'actual' => 28, 'performance' => 93],
            'Q3' => ['budget' => 25, 'actual' => 26, 'performance' => 104],
            'Q4' => ['budget' => 20, 'actual' => 18, 'performance' => 90],
        ];
    }

    private function getOverBudgetDepartments($annee)
    {
        $departments = $this->calculateDepartmentVariance($annee);
        return array_filter($departments, function ($dept) {
            return $dept['variance'] < 0; // Negative variance means over budget
        });
    }

    private function getUnderUtilizedBudgets($annee)
    {
        $departments = $this->calculateDepartmentVariance($annee);
        return array_filter($departments, function ($dept) {
            return $dept['variance'] > 1000000; // More than 1M FCFA under budget
        });
    }

    private function getHighCostActivities($annee)
    {
        $activities = $this->dashboardService->getTopActivites();
        return array_filter($activities, function ($activity) {
            return $activity['cout'] > 5000000; // Activities costing more than 5M FCFA
        });
    }

    private function calculateYearlyTrends($annee)
    {
        // Get data for the last 3 years
        $trends = [];
        for ($i = 2; $i >= 0; $i--) {
            $year = $annee - $i;
            // In a real app, this would query historical data
            $trends[] = [
                'year' => $year,
                'budget' => rand(50000000, 150000000), // Mock data
                'actual' => rand(45000000, 140000000),
            ];
        }
        return $trends;
    }

    private function calculateBudgetGrowthRate($annee)
    {
        $trends = $this->calculateYearlyTrends($annee);
        if (count($trends) < 2) return 0;

        $current = end($trends)['budget'];
        $previous = prev($trends)['budget'];

        return $previous > 0 ? (($current - $previous) / $previous) * 100 : 0;
    }

    private function generateBudgetForecast($annee)
    {
        $currentBudget = $this->dashboardService->getStats()['budget_total'];
        $growthRate = $this->calculateBudgetGrowthRate($annee);

        return [
            ($annee + 1) => $currentBudget * (1 + $growthRate / 100),
            ($annee + 2) => $currentBudget * (1 + $growthRate / 100) * (1 + $growthRate / 100),
        ];
    }
}
