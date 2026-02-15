<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Report\IntelligenceReportService;

class ReportController extends Controller
{
    public function intelligence(IntelligenceReportService $service)
    {
        $report = $service->execute();
        return response()->json($report);
    }
}
