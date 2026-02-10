<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\HourlyReport;
use App\Models\Location;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ManagementReportController extends Controller
{
    public function index(Request $request): View
    {
        $query = HourlyReport::with(['promoter', 'location', 'premiums']);

        if ($request->filled('date')) {
            $query->whereDate('report_date', $request->input('date'));
        }

        if ($request->filled('location_id')) {
            $query->where('location_id', $request->input('location_id'));
        }

        if ($request->filled('promoter_user_id')) {
            $query->where('promoter_user_id', $request->input('promoter_user_id'));
        }

        $reports = $query->orderByDesc('report_date')
            ->orderByDesc('report_hour')
            ->paginate(20)
            ->withQueryString();

        $locations = Location::orderBy('name')->get();
        $promoters = User::where('role', 'promoter')->orderBy('name')->get();

        return view('management.reports-index', [
            'reports' => $reports,
            'locations' => $locations,
            'promoters' => $promoters,
        ]);
    }

    public function show(HourlyReport $report): View
    {
        $report->load(['promoter', 'location', 'items.product', 'premiums']);

        return view('management.report-show', [
            'report' => $report,
        ]);
    }
}
