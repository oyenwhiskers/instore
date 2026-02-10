<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\KpiTarget;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KpiTargetController extends Controller
{
    public function index(): View
    {
        $promoters = User::where('role', 'promoter')
            ->orderBy('name')
            ->get();

        return view('management.kpi-targets-index', [
            'promoters' => $promoters,
        ]);
    }

    public function edit(User $user): View
    {
        $latestTarget = KpiTarget::where('promoter_user_id', $user->id)
            ->orderByDesc('period_start')
            ->first();

        return view('management.kpi-targets-edit', [
            'promoter' => $user,
            'latestTarget' => $latestTarget,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
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
                'promoter_user_id' => $user->id,
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

        return redirect()->route('management.kpi-targets.index')->with('status', 'KPI targets saved.');
    }
}
