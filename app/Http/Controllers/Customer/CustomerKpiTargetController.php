<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\KpiTarget;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerKpiTargetController extends Controller
{
    public function index(Request $request): View
    {
        $companyId = $request->user()->company_id;
        $promoters = User::where('company_id', $companyId)
            ->where('role', 'promoter')
            ->orderBy('name')
            ->get();

        return view('customer.kpi-targets.index', [
            'promoters' => $promoters,
        ]);
    }

    public function edit(Request $request, User $promoter): View
    {
        if ($promoter->company_id !== $request->user()->company_id || $promoter->role !== 'promoter') {
            abort(403, 'Unauthorized');
        }

        $latestTarget = KpiTarget::where('promoter_user_id', $promoter->id)
            ->orderByDesc('period_start')
            ->first();

        return view('customer.kpi-targets.edit', [
            'promoter' => $promoter,
            'latestTarget' => $latestTarget,
        ]);
    }

    public function update(Request $request, User $promoter): RedirectResponse
    {
        if ($promoter->company_id !== $request->user()->company_id || $promoter->role !== 'promoter') {
            abort(403, 'Unauthorized');
        }

        $data = $request->validate([
            'period_type' => ['required', 'in:daily,weekly,monthly'],
            'period_start' => ['required', 'date'],
            'target_sales_amount' => ['required', 'numeric', 'min:0'],
            'target_engagements' => ['required', 'integer', 'min:0'],
            'target_samplings' => ['required', 'integer', 'min:0'],
            'target_premium_tier1' => ['required', 'integer', 'min:0'],
            'target_premium_tier2' => ['required', 'integer', 'min:0'],
        ]);

        KpiTarget::updateOrCreate(
            [
                'promoter_user_id' => $promoter->id,
                'period_type' => $data['period_type'],
                'period_start' => $data['period_start'],
            ],
            [
                'target_sales_amount' => $data['target_sales_amount'],
                'target_engagements' => $data['target_engagements'],
                'target_samplings' => $data['target_samplings'],
                'target_premium_tier1' => $data['target_premium_tier1'],
                'target_premium_tier2' => $data['target_premium_tier2'],
            ]
        );

        return redirect()->route('customer.kpi-targets.index')->with('status', 'KPI targets saved.');
    }
}
