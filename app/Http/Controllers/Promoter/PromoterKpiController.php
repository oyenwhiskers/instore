<?php

namespace App\Http\Controllers\Promoter;

use App\Http\Controllers\Controller;
use App\Models\HourlyReport;
use App\Models\KpiTarget;
use App\Models\PremiumRedemption;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PromoterKpiController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $today = Carbon::today();

        $reportsToday = HourlyReport::where('promoter_user_id', $user->id)
            ->whereDate('report_date', $today)
            ->get();

        $totals = [
            'sales' => $reportsToday->sum('total_sales_amount'),
            'engagements' => $reportsToday->sum('engagements_count'),
            'samplings' => $reportsToday->sum('samplings_count'),
        ];

        $reportIds = $reportsToday->pluck('id');
        $premiumTier1 = PremiumRedemption::whereIn('hourly_report_id', $reportIds)
            ->where('tier', 1)
            ->sum('quantity');
        $premiumTier2 = PremiumRedemption::whereIn('hourly_report_id', $reportIds)
            ->where('tier', 2)
            ->sum('quantity');

        $latestTarget = KpiTarget::where('promoter_user_id', $user->id)
            ->orderByDesc('period_start')
            ->first();

        return view('promoter.kpi', [
            'user' => $user,
            'totals' => $totals,
            'premiumTier1' => $premiumTier1,
            'premiumTier2' => $premiumTier2,
            'latestTarget' => $latestTarget,
        ]);
    }
}
