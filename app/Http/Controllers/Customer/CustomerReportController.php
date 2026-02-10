<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\HourlyReport;
use App\Models\Location;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class CustomerReportController extends Controller
{
    public function index(Request $request): View
    {
        $companyId = $request->user()->company_id;

        $query = HourlyReport::whereHas('location', function ($builder) use ($companyId) {
            $builder->where('company_id', $companyId);
        })->with(['promoter', 'location', 'premiums']);

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

        $locations = Location::where('company_id', $companyId)->orderBy('name')->get();
        $promoters = User::where('company_id', $companyId)->where('role', 'promoter')->orderBy('name')->get();

        return view('customer.reports.index', [
            'reports' => $reports,
            'locations' => $locations,
            'promoters' => $promoters,
        ]);
    }

    public function show(Request $request, HourlyReport $report): View
    {
        $companyId = $request->user()->company_id;
        $report->load(['promoter', 'location', 'items.product', 'premiums']);

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

        $query = HourlyReport::whereHas('location', function ($builder) use ($companyId) {
            $builder->where('company_id', $companyId);
        })->with(['promoter', 'location', 'premiums']);

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
            ->get();

        $fileName = 'customer-reports-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($reports) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Date',
                'Hour',
                'Promoter',
                'Location',
                'Sales',
                'Engagements',
                'Samplings',
                'Premium Tier 1',
                'Premium Tier 2',
            ]);

            foreach ($reports as $report) {
                $tier1 = $report->premiums->where('tier', 1)->sum('quantity');
                $tier2 = $report->premiums->where('tier', 2)->sum('quantity');

                fputcsv($handle, [
                    $report->report_date->toDateString(),
                    str_pad($report->report_hour, 2, '0', STR_PAD_LEFT) . ':00',
                    $report->promoter?->name,
                    $report->location?->name,
                    $report->total_sales_amount,
                    $report->engagements_count,
                    $report->samplings_count,
                    $tier1,
                    $tier2,
                ]);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
