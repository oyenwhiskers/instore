<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\HourlyReport;
use App\Models\PremiumRedemption;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ManagementReportController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->input('status', 'active');
        $today = Carbon::today();

        $events = Event::with(['location', 'promoters', 'products', 'premiums'])
            ->orderByDesc('start_date')
            ->get();

        $eventSummaries = $events->map(function (Event $event) use ($today) {
            $status = 'completed';
            if ($today->between($event->start_date, $event->end_date)) {
                $status = 'active';
            } elseif ($today->lt($event->start_date)) {
                $status = 'upcoming';
            }

            $reportQuery = HourlyReport::where('location_id', $event->location_id)
                ->whereBetween('report_date', [$event->start_date, $event->end_date]);

            if ($event->promoters->isNotEmpty()) {
                $reportQuery->whereIn('promoter_user_id', $event->promoters->pluck('id'));
            }

            $reports = $reportQuery->with(['items', 'premiums'])->get();
            $reportIds = $reports->pluck('id');

            $totalSales = $reports->sum('total_sales_amount');
            $totalEngagements = $reports->sum('engagements_count');
            $totalSamplings = $reports->sum('samplings_count');
            $totalReports = $reports->count();

            $premiumTotal = $reportIds->isNotEmpty()
                ? PremiumRedemption::whereIn('hourly_report_id', $reportIds)->sum('quantity')
                : 0;

            $lastReport = $reports->sortByDesc('created_at')->first();

            return [
                'event' => $event,
                'status' => $status,
                'total_sales' => $totalSales,
                'total_engagements' => $totalEngagements,
                'total_samplings' => $totalSamplings,
                'total_premiums' => $premiumTotal,
                'total_reports' => $totalReports,
                'last_report' => $lastReport,
            ];
        });

        $statusCounts = [
            'active' => $eventSummaries->where('status', 'active')->count(),
            'upcoming' => $eventSummaries->where('status', 'upcoming')->count(),
            'completed' => $eventSummaries->where('status', 'completed')->count(),
            'all' => $eventSummaries->count(),
        ];

        if ($status !== 'all') {
            $eventSummaries = $eventSummaries->where('status', $status)->values();
        } else {
            $eventSummaries = $eventSummaries->values();
        }

        $totalSalesAll = $eventSummaries->sum('total_sales');
        $totalPremiumsAll = $eventSummaries->sum('total_premiums');

        return view('management.reports-index', [
            'eventSummaries' => $eventSummaries,
            'status' => $status,
            'statusCounts' => $statusCounts,
            'totalSalesAll' => $totalSalesAll,
            'totalPremiumsAll' => $totalPremiumsAll,
        ]);
    }

    public function show(Event $event): View
    {
        $event->load(['location', 'promoters', 'products', 'premiums', 'stockMovements.product']);

        $today = Carbon::today();
        $now = Carbon::now();

        $hourlyReports = HourlyReport::where('location_id', $event->location_id)
            ->whereBetween('report_date', [$event->start_date, $event->end_date])
            ->when($event->promoters->isNotEmpty(), function ($query) use ($event) {
                $query->whereIn('promoter_user_id', $event->promoters->pluck('id'));
            })
            ->with(['promoter', 'items.product', 'premiums.premium'])
            ->orderByDesc('report_date')
            ->orderByDesc('report_hour')
            ->get();

        $todayReports = $hourlyReports->where('report_date', $today);

        $actualSales = $hourlyReports->sum('total_sales_amount');
        $actualEngagements = $hourlyReports->sum('engagements_count');
        $actualSamplings = $hourlyReports->sum('samplings_count');

        $todaySales = $todayReports->sum('total_sales_amount');
        $todayEngagements = $todayReports->sum('engagements_count');
        $todaySamplings = $todayReports->sum('samplings_count');

        $hourlyBreakdown = $todayReports->groupBy('report_hour')->map(function ($reports) {
            return [
                'sales' => $reports->sum('total_sales_amount'),
                'engagements' => $reports->sum('engagements_count'),
                'samplings' => $reports->sum('samplings_count'),
            ];
        })->sortKeys();

        $productSales = $hourlyReports->flatMap(fn($r) => $r->items)
            ->groupBy('product_id')
            ->map(function ($items) {
                return [
                    'product' => $items->first()?->product,
                    'quantity' => $items->sum('quantity_sold'),
                ];
            })
            ->sortByDesc('quantity')
            ->values();

        $premiumRedemptions = $hourlyReports->flatMap(fn($r) => $r->premiums)
            ->filter(fn($premium) => $premium->premium)
            ->groupBy('premium_id')
            ->map(function ($items) {
                return [
                    'premium' => $items->first()?->premium,
                    'quantity' => $items->sum('quantity'),
                ];
            })
            ->sortByDesc('quantity')
            ->values();

        $promoterPerformance = $hourlyReports->groupBy('promoter_user_id')->map(function ($reports) {
            return [
                'promoter' => $reports->first()?->promoter,
                'sales' => $reports->sum('total_sales_amount'),
                'engagements' => $reports->sum('engagements_count'),
                'samplings' => $reports->sum('samplings_count'),
                'report_count' => $reports->count(),
                'last_report' => $reports->sortByDesc('report_date')->sortByDesc('report_hour')->first(),
            ];
        })->sortByDesc('sales')->values();

        $currentHour = (int) $now->format('H');
        $expectedReports = $event->promoters->count();
        $currentHourReports = $todayReports->where('report_hour', $currentHour)->count();

        $stockMovementsAll = $event->stockMovements;
        $stockBalances = $event->products
            ->mapWithKeys(function ($product) use ($stockMovementsAll) {
                $balance = $stockMovementsAll
                    ->where('product_id', $product->id)
                    ->reduce(function ($carry, $movement) {
                        $direction = $movement->movement_type === 'out' ? -1 : 1;
                        return $carry + ($movement->quantity * $direction);
                    }, 0);

                return [$product->id => $balance];
            });

        $lowStockProducts = $stockBalances->filter(fn($balance) => $balance > 0 && $balance < 10)->count();
        $outOfStockProducts = $stockBalances->filter(fn($balance) => $balance <= 0)->count();

        $recentReports = $hourlyReports->take(8);

        return view('management.report-show', [
            'event' => $event,
            'hourlyReports' => $hourlyReports,
            'todayReports' => $todayReports,
            'actualSales' => $actualSales,
            'actualEngagements' => $actualEngagements,
            'actualSamplings' => $actualSamplings,
            'todaySales' => $todaySales,
            'todayEngagements' => $todayEngagements,
            'todaySamplings' => $todaySamplings,
            'hourlyBreakdown' => $hourlyBreakdown,
            'productSales' => $productSales,
            'premiumRedemptions' => $premiumRedemptions,
            'promoterPerformance' => $promoterPerformance,
            'expectedReports' => $expectedReports,
            'currentHourReports' => $currentHourReports,
            'lowStockProducts' => $lowStockProducts,
            'outOfStockProducts' => $outOfStockProducts,
            'recentReports' => $recentReports,
        ]);
    }
}
