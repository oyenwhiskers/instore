<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventPromoterPremiumTarget;
use App\Models\HourlyReport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class CustomerReportController extends Controller
{
    public function index(Request $request): View
    {
        $companyId = $request->user()->company_id;
        [$summaries, $filters, $totals] = $this->buildEventSummaries($request, $companyId);

        return view('customer.reports.index', [
            'summaries' => $summaries,
            'filters' => $filters,
            'totals' => $totals,
        ]);
    }

    public function show(Request $request, HourlyReport $report): View
    {
        $companyId = $request->user()->company_id;
        $report->load(['promoter', 'location', 'items.product', 'premiums.premium']);

        if ($report->location?->company_id !== $companyId) {
            abort(403, 'Unauthorized');
        }

        return view('customer.reports.show', [
            'report' => $report,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $companyId = $request->user()->company_id;
        [$summaries] = $this->buildEventSummaries($request, $companyId);

        $fileName = 'event-sales-summary-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($summaries) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Brand',
                'Campaign',
                'Location',
                'Date',
                'Target Sales',
                'Actual Sales',
                'Target Engagements',
                'Actual Engagements',
                'Target Samplings',
                'Actual Samplings',
                'Target Redemptions',
                'Actual Redemptions',
                'Target Conversion',
                'Actual Conversion',
                'Status',
                'Notes',
                'Insight',
            ]);

            foreach ($summaries as $summary) {
                $targets = $summary['targets'];
                $actuals = $summary['actuals'];
                $status = $summary['status'];

                fputcsv($handle, [
                    $summary['brand'],
                    $summary['event']->name,
                    $summary['event']->location?->name,
                    $summary['date'],
                    $targets['sales'],
                    $actuals['sales'],
                    $targets['engagements'],
                    $actuals['engagements'],
                    $targets['samplings'],
                    $actuals['samplings'],
                    $targets['redemptions'],
                    $actuals['redemptions'],
                    $targets['conversion'],
                    $actuals['conversion'],
                    $status['overall'],
                    implode(' | ', $summary['notes']),
                    $summary['insight'],
                ]);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function exportPdf(Request $request): View
    {
        $companyId = $request->user()->company_id;
        [$summaries, $filters, $totals] = $this->buildEventSummaries($request, $companyId);

        return view('customer.reports.summary-pdf', [
            'summaries' => $summaries,
            'filters' => $filters,
            'totals' => $totals,
        ]);
    }

    private function buildEventSummaries(Request $request, int $companyId): array
    {
        $filterDate = $request->filled('date') ? Carbon::parse($request->input('date')) : null;
        $status = $request->input('status', 'all');
        $today = Carbon::today();

        $events = Event::where('company_id', $companyId)
            ->with([
                'location',
                'products.brandClient',
                'promoters',
                'premiums',
                'stockMovements',
                'promoterKpis',
            ])
            ->orderByDesc('start_date')
            ->get();

        $summaries = collect();
        $totalSales = 0;
        $totalRedemptions = 0;

        foreach ($events as $event) {
            $eventStatus = 'completed';
            if ($today->between($event->start_date, $event->end_date)) {
                $eventStatus = 'active';
            } elseif ($today->lt($event->start_date)) {
                $eventStatus = 'upcoming';
            }

            if ($status !== 'all' && $status !== $eventStatus) {
                continue;
            }

            $brandGroups = $event->products
                ->groupBy(fn($product) => $product->brandClient?->name ?? 'Unbranded');

            $eventDays = Carbon::parse($event->start_date)->diffInDays(Carbon::parse($event->end_date)) + 1;
            $eventDays = max($eventDays, 1);

            $totalTargets = [
                'sales' => (float) $event->promoterKpis->sum('target_sales_amount'),
                'engagements' => (int) $event->promoterKpis->sum('target_engagements'),
                'samplings' => (int) $event->promoterKpis->sum('target_samplings'),
                'redemptions' => (int) EventPromoterPremiumTarget::where('event_id', $event->id)->sum('target_qty'),
            ];

            $dailyTargets = [
                'sales' => round($totalTargets['sales'] / $eventDays, 2),
                'engagements' => (int) round($totalTargets['engagements'] / $eventDays),
                'samplings' => (int) round($totalTargets['samplings'] / $eventDays),
                'redemptions' => (int) round($totalTargets['redemptions'] / $eventDays),
            ];

            $conversionTarget = $dailyTargets['engagements'] > 0
                ? round($dailyTargets['sales'] / $dailyTargets['engagements'], 2)
                : 0;

            $eventReports = HourlyReport::where('location_id', $event->location_id)
                ->whereBetween('report_date', [$event->start_date, $event->end_date])
                ->when($event->promoters->isNotEmpty(), function ($query) use ($event) {
                    $query->whereIn('promoter_user_id', $event->promoters->pluck('id'));
                })
                ->with(['items.product.brandClient', 'premiums'])
                ->get();

            $reportsByDate = $eventReports->groupBy(fn($report) => $report->report_date->toDateString());

            $outOfStockNames = $event->stockMovements
                ->groupBy('product_id')
                ->map(function ($movements) {
                    return $movements->reduce(function ($carry, $movement) {
                        $direction = $movement->movement_type === 'out' ? -1 : 1;
                        return $carry + ($movement->quantity * $direction);
                    }, 0);
                })
                ->filter(fn($balance) => $balance <= 0)
                ->keys()
                ->map(function ($productId) use ($event) {
                    return $event->products->firstWhere('id', $productId)?->name;
                })
                ->filter()
                ->values();

            $dateCursor = Carbon::parse($event->start_date)->startOfDay();
            $endCursor = Carbon::parse($event->end_date)->startOfDay();

            while ($dateCursor <= $endCursor) {
                if ($filterDate && !$dateCursor->isSameDay($filterDate)) {
                    $dateCursor->addDay();
                    continue;
                }

                $dateKey = $dateCursor->toDateString();
                $dailyReports = $reportsByDate->get($dateKey, collect());
                $dailyItems = $dailyReports->flatMap(fn($report) => $report->items);
                $dailyPremiums = $dailyReports->flatMap(fn($report) => $report->premiums);

                $totalItemQty = $dailyItems->sum('quantity_sold');
                $dailySalesTotal = $dailyReports->sum('total_sales_amount');
                $dailyEngagementsTotal = $dailyReports->sum('engagements_count');
                $dailySamplingsTotal = $dailyReports->sum('samplings_count');
                $dailyRedemptionsTotal = $dailyPremiums->sum('quantity');

                foreach ($brandGroups as $brandName => $brandProducts) {
                    $brandProductIds = $brandProducts->pluck('id');
                    $brandItems = $dailyItems->filter(fn($item) => $brandProductIds->contains($item->product_id));
                    $brandQty = $brandItems->sum('quantity_sold');

                    $brandSales = $brandItems->sum(function ($item) use ($event) {
                        $product = $event->products->firstWhere('id', $item->product_id);
                        $price = (float) ($product?->pivot?->unit_price ?? 0);
                        return $price * $item->quantity_sold;
                    });

                    $ratio = $totalItemQty > 0 ? ($brandQty / $totalItemQty) : 0;
                    $brandEngagements = (int) round($dailyEngagementsTotal * $ratio);
                    $brandSamplings = (int) round($dailySamplingsTotal * $ratio);
                    $brandRedemptions = (int) round($dailyRedemptionsTotal * $ratio);

                    $brandCount = max($brandGroups->count(), 1);
                    $targets = [
                        'sales' => $dailyTargets['sales'] / $brandCount,
                        'engagements' => (int) round($dailyTargets['engagements'] / $brandCount),
                        'samplings' => (int) round($dailyTargets['samplings'] / $brandCount),
                        'redemptions' => (int) round($dailyTargets['redemptions'] / $brandCount),
                        'conversion' => $conversionTarget,
                    ];

                    $actualSales = round($brandSales > 0 ? $brandSales : ($dailySalesTotal * $ratio), 2);
                    $actuals = [
                        'sales' => $actualSales,
                        'engagements' => $brandEngagements,
                        'samplings' => $brandSamplings,
                        'redemptions' => $brandRedemptions,
                        'conversion' => $brandEngagements > 0 ? round($actualSales / $brandEngagements, 2) : 0,
                    ];

                    $status = $this->evaluateKpiStatus($targets, $actuals);

                    $notes = [];
                    if ($dateCursor->lte($today) && $dailyReports->isEmpty()) {
                        $notes[] = 'No submissions recorded for this day.';
                    }
                    if ($dateCursor->lte($today) && $dailyReports->count() < $event->promoters->count()) {
                        $notes[] = 'Coverage below roster.';
                    }
                    if ($outOfStockNames->isNotEmpty()) {
                        $notes[] = 'Stock depletion: ' . $outOfStockNames->implode(', ');
                    }

                    $insight = $this->buildInsight($status, $notes);

                    $summaries->push([
                        'event' => $event,
                        'brand' => $brandName,
                        'date' => $dateKey,
                        'targets' => $targets,
                        'actuals' => $actuals,
                        'status' => $status,
                        'notes' => $notes,
                        'insight' => $insight,
                        'event_status' => $eventStatus,
                    ]);

                    $totalSales += $actualSales;
                    $totalRedemptions += $actuals['redemptions'];
                }

                $dateCursor->addDay();
            }
        }

        $filters = [
            'date' => $filterDate?->toDateString(),
            'status' => $status,
        ];

        $totals = [
            'sales' => $totalSales,
            'redemptions' => $totalRedemptions,
        ];

        return [$summaries, $filters, $totals];
    }

    private function evaluateKpiStatus(array $targets, array $actuals): array
    {
        $evaluate = function (float|int $target, float|int $actual): string {
            if ($target <= 0) {
                return $actual > 0 ? 'above' : 'on_track';
            }

            if ($actual >= $target * 1.05) {
                return 'above';
            }

            if ($actual >= $target * 0.85) {
                return 'on_track';
            }

            return 'below';
        };

        $status = [
            'sales' => $evaluate($targets['sales'], $actuals['sales']),
            'engagements' => $evaluate($targets['engagements'], $actuals['engagements']),
            'samplings' => $evaluate($targets['samplings'], $actuals['samplings']),
            'redemptions' => $evaluate($targets['redemptions'], $actuals['redemptions']),
            'conversion' => $evaluate($targets['conversion'], $actuals['conversion']),
        ];

        $status['overall'] = in_array('below', $status, true)
            ? 'below'
            : (in_array('on_track', $status, true) ? 'on_track' : 'above');

        return $status;
    }

    private function buildInsight(array $status, array $notes): string
    {
        if (in_array('below', $status, true)) {
            if ($status['sales'] === 'below') {
                return 'Boost conversion with targeted upsell during peak hours.';
            }
            if ($status['engagements'] === 'below') {
                return 'Increase sampling touches to lift engagement volume.';
            }
            if ($status['redemptions'] === 'below') {
                return 'Reinforce premium mechanics at the point of sale.';
            }
        }

        if (!empty($notes)) {
            return 'Address operational blockers to stabilize performance.';
        }

        return 'Maintain momentum and replicate best-performing outlets.';
    }
}
