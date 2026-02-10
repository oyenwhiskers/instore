<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\HourlyReport;
use App\Models\Location;
use App\Models\PremiumRedemption;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\View\View;

class ManagementDashboardController extends Controller
{
    public function index(): View
    {
        $today = Carbon::today();

        $reportsToday = HourlyReport::whereDate('report_date', $today)->get();
        $reportIds = $reportsToday->pluck('id');

        $totals = [
            'sales' => $reportsToday->sum('total_sales_amount'),
            'engagements' => $reportsToday->sum('engagements_count'),
            'samplings' => $reportsToday->sum('samplings_count'),
        ];

        $premiumTotal = PremiumRedemption::whereIn('hourly_report_id', $reportIds)
            ->sum('quantity');

        $promoterCount = User::where('role', 'promoter')->count();
        $locationCount = Location::count();

        return view('management.dashboard', [
            'totals' => $totals,
            'premiumTotal' => $premiumTotal,
            'promoterCount' => $promoterCount,
            'locationCount' => $locationCount,
        ]);
    }
}
