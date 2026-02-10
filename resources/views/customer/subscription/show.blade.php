@extends('layouts.app')

@section('page_title', 'Subscription')
@section('page_desc', 'Plan details, billing cycle, and current usage.')

@section('content')
<div class="split-view">
    <div class="card">
        <div class="stat-label">Company</div>
        <div class="form-grid text-sm">
            <div><strong>Name:</strong> {{ $company->name }}</div>
            <div><strong>Status:</strong> {{ ucfirst($company->status) }}</div>
            <div><strong>Billing Cycle:</strong> {{ ucfirst($company->billing_cycle) }}</div>
            <div><strong>Subscription Ends:</strong> {{ $company->subscription_ends_at?->toDateString() ?? 'N/A' }}</div>
        </div>
    </div>

    <div class="card">
        <div class="stat-label">Plan</div>
        <div class="form-grid text-sm">
            <div><strong>Name:</strong> {{ $company->plan?->name ?? 'Not set' }}</div>
            <div><strong>Monthly Price:</strong> RM {{ number_format($company->plan?->price_monthly ?? 0, 2) }}</div>
            <div><strong>Features:</strong> {{ $company->plan?->features ? implode(', ', $company->plan->features) : 'N/A' }}</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="stat-label">Usage</div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Module</th>
                    <th>Current Usage</th>
                    <th>Plan Limit</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Brand Clients</td>
                    <td>{{ $usage['brand_clients'] }}</td>
                    <td>{{ $company->plan?->isUnlimited($company->plan?->max_customers) ? 'Unlimited' : ($company->plan?->max_customers ?? '-') }}</td>
                </tr>
                <tr>
                    <td>Products</td>
                    <td>{{ $usage['products'] }}</td>
                    <td>{{ $company->plan?->isUnlimited($company->plan?->max_products) ? 'Unlimited' : ($company->plan?->max_products ?? '-') }}</td>
                </tr>
                <tr>
                    <td>Locations</td>
                    <td>{{ $usage['locations'] }}</td>
                    <td>{{ $company->plan?->isUnlimited($company->plan?->max_locations) ? 'Unlimited' : ($company->plan?->max_locations ?? '-') }}</td>
                </tr>
                <tr>
                    <td>Promoters</td>
                    <td>{{ $usage['promoters'] }}</td>
                    <td>{{ $company->plan?->isUnlimited($company->plan?->max_dashboards) ? 'Unlimited' : ($company->plan?->max_dashboards ?? '-') }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
