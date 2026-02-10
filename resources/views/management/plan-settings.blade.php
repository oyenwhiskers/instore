@extends('layouts.app')

@section('page_title', 'Plan Settings')
@section('page_desc', 'Manage pricing tiers and the limits that control access.')

@section('content')
<div class="card">
    <div class="stat-label">Active Package</div>
    <div class="form-grid">
        <div>
            <div class="stat-value">{{ $activePlan?->name ?? 'Not Set' }}</div>
            <p class="muted text-sm">RM {{ number_format($activePlan?->price_monthly ?? 0) }} / month</p>
        </div>
        <div>
            <div class="stat-label">Inventory</div>
            <div class="stat-value">{{ ucfirst($activePlan?->inventory_level ?? 'basic') }}</div>
        </div>
        <div>
            <div class="stat-label">Dashboards</div>
            <div class="stat-value">{{ $activePlan?->max_dashboards ?? 'Unlimited' }}</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="stat-label">Tier Limits & Features</div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Plan</th>
                    <th>Monthly Price</th>
                    <th>Max Products</th>
                    <th>Max Locations</th>
                    <th>Max Dashboards</th>
                    <th>Max Customers</th>
                    <th>Inventory</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($plans as $plan)
                    <tr>
                        <td>{{ $plan->name }}</td>
                        <td>RM {{ number_format($plan->price_monthly) }}</td>
                        <td>{{ $plan->max_products ?? 'Unlimited' }}</td>
                        <td>{{ $plan->max_locations ?? 'Unlimited' }}</td>
                        <td>{{ $plan->max_dashboards ?? 'Unlimited' }}</td>
                        <td>{{ $plan->max_customers ?? 'Unlimited' }}</td>
                        <td>{{ ucfirst($plan->inventory_level) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
