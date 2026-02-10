@extends('layouts.app')

@section('page_title', 'KPI Progress')
@section('page_desc', 'Track KPI progress for ' . $promoter->name . '.')
@section('page_actions')
    <a href="{{ route('customer.kpi-targets.index') }}" class="btn btn-secondary">Back to KPI List</a>
@endsection

@section('content')
<div class="card">
    <form method="GET" class="form-section">
        <div class="stat-label">Select Date</div>
        <div class="form-grid">
            <div class="form-group">
                <label>Date</label>
                <input type="date" name="date" value="{{ $date->toDateString() }}" class="input">
            </div>
            <div class="form-group">
                <label>Period</label>
                <select name="period" class="select">
                    <option value="day" @selected($period === 'day')>Day</option>
                    <option value="week" @selected($period === 'week')>Week</option>
                    <option value="month" @selected($period === 'month')>Month</option>
                </select>
            </div>
            <div class="form-group" style="align-self: end;">
                <button class="btn btn-primary" type="submit">Load</button>
            </div>
        </div>
    </form>
</div>

<div class="card-grid">
    <div class="card">
        <div class="stat-label">Sales</div>
        <div class="stat-value">RM {{ number_format($totals['sales'], 2) }}</div>
    </div>
    <div class="card">
        <div class="stat-label">Engagements</div>
        <div class="stat-value">{{ $totals['engagements'] }}</div>
    </div>
    <div class="card">
        <div class="stat-label">Samplings</div>
        <div class="stat-value">{{ $totals['samplings'] }}</div>
    </div>
    <div class="card">
        <div class="stat-label">Premiums</div>
        <div class="stat-value">T1 {{ $premiumTier1 }} / T2 {{ $premiumTier2 }}</div>
    </div>
</div>

<div class="card">
    <div class="stat-label">Targets vs Actuals</div>
    <div class="text-sm muted">Range: {{ $startDate->toDateString() }} to {{ $endDate->toDateString() }}</div>
    @if ($latestTarget)
        <div class="form-grid text-sm">
            <div>Sales Target: RM {{ number_format($latestTarget->target_sales_amount, 2) }}</div>
            <div>Engagements Target: {{ $latestTarget->target_engagements }}</div>
            <div>Samplings Target: {{ $latestTarget->target_samplings }}</div>
            <div>Premium T1 Target: {{ $latestTarget->target_premium_tier1 }}</div>
            <div>Premium T2 Target: {{ $latestTarget->target_premium_tier2 }}</div>
        </div>
    @else
        <p class="muted text-sm">No KPI targets assigned yet.</p>
    @endif
</div>
@endsection
