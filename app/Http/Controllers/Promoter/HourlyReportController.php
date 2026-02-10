<?php

namespace App\Http\Controllers\Promoter;

use App\Http\Controllers\Controller;
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

        return view('promoter.report-create', [
            'products' => $products,
            'profile' => $profile,
            'assignment' => $assignment,
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
            'premium_tier1_qty' => ['nullable', 'integer', 'min:0'],
            'premium_tier2_qty' => ['nullable', 'integer', 'min:0'],
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

        $tier1Qty = (int) ($data['premium_tier1_qty'] ?? 0);
        $tier2Qty = (int) ($data['premium_tier2_qty'] ?? 0);

        if ($tier1Qty > 0 && $data['total_sales_amount'] < 10) {
            return back()->withErrors([
                'premium_tier1_qty' => 'Tier 1 premium requires sales of at least RM10.',
            ])->withInput();
        }

        if ($tier2Qty > 0 && $data['total_sales_amount'] < 15) {
            return back()->withErrors([
                'premium_tier2_qty' => 'Tier 2 premium requires sales of at least RM15.',
            ])->withInput();
        }

        $report = HourlyReport::create([
            'promoter_user_id' => $user->id,
            'location_id' => $locationId,
            'report_date' => Carbon::parse($data['report_date'])->toDateString(),
            'report_hour' => $data['report_hour'],
            'total_sales_amount' => $data['total_sales_amount'],
            'engagements_count' => $data['engagements_count'],
            'samplings_count' => $data['samplings_count'],
        ]);

        foreach ($data['items'] ?? [] as $productId => $quantity) {
            $quantity = (int) $quantity;
            if ($quantity > 0) {
                HourlyReportItem::create([
                    'hourly_report_id' => $report->id,
                    'product_id' => $productId,
                    'quantity_sold' => $quantity,
                ]);
            }
        }

        if ($tier1Qty > 0) {
            PremiumRedemption::create([
                'hourly_report_id' => $report->id,
                'tier' => 1,
                'quantity' => $tier1Qty,
            ]);
        }

        if ($tier2Qty > 0) {
            PremiumRedemption::create([
                'hourly_report_id' => $report->id,
                'tier' => 2,
                'quantity' => $tier2Qty,
            ]);
        }

        return redirect()->route('promoter.reports.history')->with('status', 'Report submitted.');
    }

    public function history(Request $request): View
    {
        $reports = HourlyReport::where('promoter_user_id', $request->user()->id)
            ->with(['location', 'items.product', 'premiums'])
            ->orderByDesc('report_date')
            ->orderByDesc('report_hour')
            ->paginate(20);

        return view('promoter.report-history', [
            'reports' => $reports,
        ]);
    }
}
