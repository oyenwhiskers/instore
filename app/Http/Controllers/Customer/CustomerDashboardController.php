<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\HourlyReport;
use App\Models\Location;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $companyId = $request->user()->company_id;

        $reportScope = HourlyReport::whereHas('location', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        });

        $totals = [
            'sales' => (float) (clone $reportScope)->sum('total_sales_amount'),
            'engagements' => (int) (clone $reportScope)->sum('engagements_count'),
            'samplings' => (int) (clone $reportScope)->sum('samplings_count'),
        ];

        $recentReports = (clone $reportScope)
            ->with(['promoter', 'location', 'premiums'])
            ->orderByDesc('report_date')
            ->orderByDesc('report_hour')
            ->limit(5)
            ->get();

        $topLocations = DB::table('hourly_reports')
            ->join('locations', 'hourly_reports.location_id', '=', 'locations.id')
            ->where('locations.company_id', $companyId)
            ->groupBy('locations.id', 'locations.name')
            ->select(
                'locations.name as location_name',
                DB::raw('sum(hourly_reports.total_sales_amount) as total_sales'),
                DB::raw('sum(hourly_reports.engagements_count) as total_engagements'),
                DB::raw('sum(hourly_reports.samplings_count) as total_samplings')
            )
            ->orderByDesc('total_sales')
            ->limit(5)
            ->get();

        $promoterCount = User::where('company_id', $companyId)->where('role', 'promoter')->count();
        $locationCount = Location::where('company_id', $companyId)->count();

        return view('customer.dashboard', [
            'totals' => $totals,
            'promoterCount' => $promoterCount,
            'locationCount' => $locationCount,
            'recentReports' => $recentReports,
            'topLocations' => $topLocations,
        ]);
    }
}
