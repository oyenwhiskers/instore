<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\PlanSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlanSettingsController extends Controller
{
    public function index(): View
    {
        $plans = Plan::orderBy('price_monthly')->get();
        $activePlan = PlanSetting::current();

        return view('management.plan-settings', [
            'plans' => $plans,
            'activePlan' => $activePlan,
        ]);
    }

    public function update(Request $request, Plan $plan): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price_monthly' => ['required', 'integer', 'min:0'],
            'max_products' => ['nullable', 'integer', 'min:1'],
            'max_locations' => ['nullable', 'integer', 'min:1'],
            'max_dashboards' => ['nullable', 'integer', 'min:1'],
            'max_customers' => ['nullable', 'integer', 'min:1'],
            'inventory_level' => ['required', 'in:basic,full'],
        ]);

        $plan->update($data);

        return back()->with('status', 'Plan updated.');
    }

    public function setActive(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'active_plan_id' => ['required', 'exists:plans,id'],
        ]);

        $setting = PlanSetting::query()->first();
        if (!$setting) {
            PlanSetting::create(['active_plan_id' => $data['active_plan_id']]);
        } else {
            $setting->update(['active_plan_id' => $data['active_plan_id']]);
        }

        return back()->with('status', 'Active plan updated.');
    }
}
