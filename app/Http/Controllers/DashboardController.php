<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService,
    ) {}

    /**
     * Display the application dashboard.
     */
    public function index(Request $request): View
    {
        $summary = $this->dashboardService->getSummary($request->user());

        return view('dashboard.index', [
            'user' => $request->user(),
            'summary' => $summary,
        ]);
    }
}
