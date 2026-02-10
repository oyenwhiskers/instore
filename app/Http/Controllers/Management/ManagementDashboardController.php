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

        $premiumTier1 = PremiumRedemption::whereIn('hourly_report_id', $reportIds)
            ->where('tier', 1)
            ->sum('quantity');
        $premiumTier2 = PremiumRedemption::whereIn('hourly_report_id', $reportIds)
            ->where('tier', 2)
            ->sum('quantity');

        $promoterCount = User::where('role', 'promoter')->count();
        $locationCount = Location::count();

        return view('management.dashboard', [
            'totals' => $totals,
            'premiumTier1' => $premiumTier1,
            'premiumTier2' => $premiumTier2,
            'promoterCount' => $promoterCount,
            'locationCount' => $locationCount,
        ]);
    }
}
