<?php

namespace App\Http\Controllers;

use App\Services\DashboardDataService;

class DashboardController extends Controller
{
    protected $dashboardService;

    public function __construct(DashboardDataService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function index()
    {
        return view('dashboard', $this->dashboardService->getDashboardPayload());
    }
}
