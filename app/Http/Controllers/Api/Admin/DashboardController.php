<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboardService,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $overview = $this->dashboardService->getOverview([
            'from' => $request->get('from'),
            'to' => $request->get('to'),
        ]);

        return response()->json(['data' => $overview]);
    }
}
