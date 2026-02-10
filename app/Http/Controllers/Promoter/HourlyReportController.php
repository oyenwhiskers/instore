<?php

namespace App\Http\Controllers\Promoter;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventStockMovement;
use App\Models\HourlyReport;
use App\Models\HourlyReportItem;
use App\Models\PremiumRedemption;
use App\Models\Product;
use App\Models\PromoterAssignment;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HourlyReportController extends Controller
{
    public function create(Request $request): View
    {
        $profile = $request->user()->promoterProfile;
        $assignment = PromoterAssignment::where('user_id', $request->user()->id)
            ->with('location.products')
            ->first();

        $products = $assignment?->location?->products->where('is_active', true)->values();
        if (!$products || $products->isEmpty()) {
            $products = Product::where('is_active', true)->orderBy('name')->get();
        }

        $event = null;
        if ($assignment?->location_id) {
            $event = Event::where('location_id', $assignment->location_id)
                ->whereDate('start_date', '<=', now())
                ->whereDate('end_date', '>=', now())
                ->with('premiums')
                ->orderByDesc('start_date')
                ->first();
        }

        $eventPremiums = $event?->premiums ?? collect();

        return view('promoter.report-create', [
            'products' => $products,
            'profile' => $profile,
            'assignment' => $assignment,
            'eventPremiums' => $eventPremiums,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'report_date' => ['required', 'date'],
            'report_hour' => ['required', 'integer', 'min:0', 'max:23'],
            'total_sales_amount' => ['required', 'numeric', 'min:0'],
            'engagements_count' => ['required', 'integer', 'min:0'],
            'samplings_count' => ['required', 'integer', 'min:0'],
            'items' => ['array'],
            'items.*' => ['nullable', 'integer', 'min:0'],
            'premiums' => ['array'],
            'premiums.*' => ['nullable', 'integer', 'min:0'],
        ]);

        $assignment = PromoterAssignment::where('user_id', $user->id)->first();
        $locationId = $assignment?->location_id;

        if (!$locationId) {
            return back()->withErrors([
                'location' => 'No assigned location found. Please contact management.',
            ])->withInput();
        }

        $duplicate = HourlyReport::where('promoter_user_id', $user->id)
            ->whereDate('report_date', $data['report_date'])
            ->where('report_hour', $data['report_hour'])
            ->exists();

        if ($duplicate) {
            return back()->withErrors([
                'report_hour' => 'You already submitted a report for this hour.',
            ])->withInput();
        }

        $reportDate = Carbon::parse($data['report_date'])->toDateString();

        $report = HourlyReport::create([
            'promoter_user_id' => $user->id,
            'location_id' => $locationId,
            'report_date' => $reportDate,
            'report_hour' => $data['report_hour'],
            'total_sales_amount' => $data['total_sales_amount'],
            'engagements_count' => $data['engagements_count'],
            'samplings_count' => $data['samplings_count'],
        ]);

        $event = Event::where('location_id', $locationId)
            ->whereDate('start_date', '<=', $reportDate)
            ->whereDate('end_date', '>=', $reportDate)
            ->with(['products', 'premiums'])
            ->orderByDesc('start_date')
            ->first();

        foreach ($data['items'] ?? [] as $productId => $quantity) {
            $quantity = (int) $quantity;
            if ($quantity > 0) {
                HourlyReportItem::create([
                    'hourly_report_id' => $report->id,
                    'product_id' => $productId,
                    'quantity_sold' => $quantity,
                ]);

                if ($event && $event->products->contains('id', (int) $productId)) {
                    EventStockMovement::create([
                        'event_id' => $event->id,
                        'product_id' => (int) $productId,
                        'movement_type' => 'out',
                        'quantity' => $quantity,
                        'notes' => 'Stock out: ' . $user->name . ' sold ' . $quantity,
                        'created_by' => $user->id,
                    ]);
                }
            }
        }

        $allowedPremiumIds = $event?->premiums->pluck('id')->all() ?? [];
        foreach ($data['premiums'] ?? [] as $premiumId => $quantity) {
            $quantity = (int) $quantity;
            if ($quantity <= 0) {
                continue;
            }

            if (!in_array((int) $premiumId, $allowedPremiumIds, true)) {
                continue;
            }

            PremiumRedemption::create([
                'hourly_report_id' => $report->id,
                'premium_id' => (int) $premiumId,
                'tier' => 1,
                'quantity' => $quantity,
            ]);
        }

        return redirect()->route('promoter.reports.history')->with('status', 'Report submitted.');
    }

    public function history(Request $request): View
    {
        $reports = HourlyReport::where('promoter_user_id', $request->user()->id)
            ->with(['location', 'items.product', 'premiums.premium'])
            ->orderByDesc('report_date')
            ->orderByDesc('report_hour')
            ->paginate(20);

        return view('promoter.report-history', [
            'reports' => $reports,
        ]);
    }
}
