<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\HourlyReport;
use App\Models\KpiTarget;
use App\Models\PremiumRedemption;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerKpiProgressController extends Controller
{
    public function show(Request $request, User $promoter): View
    {
        if ($promoter->company_id !== $request->user()->company_id || $promoter->role !== 'promoter') {
            abort(403, 'Unauthorized');
        }

        $period = $request->input('period', 'day');
        $date = $request->filled('date')
            ? Carbon::parse($request->input('date'))
            : Carbon::today();

        if ($period === 'week') {
            $startDate = $date->copy()->startOfWeek();
            $endDate = $date->copy()->endOfWeek();
        } elseif ($period === 'month') {
            $startDate = $date->copy()->startOfMonth();
            $endDate = $date->copy()->endOfMonth();
        } else {
            $startDate = $date->copy();
            $endDate = $date->copy();
            $period = 'day';
        }

        $reports = HourlyReport::where('promoter_user_id', $promoter->id)
            ->whereBetween('report_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get();

        $totals = [
            'sales' => $reports->sum('total_sales_amount'),
            'engagements' => $reports->sum('engagements_count'),
            'samplings' => $reports->sum('samplings_count'),
        ];

        $reportIds = $reports->pluck('id');
        $premiumTotal = PremiumRedemption::whereIn('hourly_report_id', $reportIds)
            ->sum('quantity');

        $latestTarget = KpiTarget::where('promoter_user_id', $promoter->id)
            ->orderByDesc('period_start')
            ->first();

        return view('customer.kpi-progress.show', [
            'promoter' => $promoter,
            'date' => $date,
            'period' => $period,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'totals' => $totals,
            'premiumTotal' => $premiumTotal,
            'latestTarget' => $latestTarget,
        ]);
    }
}
