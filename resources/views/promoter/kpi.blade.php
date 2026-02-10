@extends('layouts.app')

@section('page_title', 'KPI Progress')
@section('page_desc', 'Monitor your daily progress against assigned KPI targets.')

@section('content')
<div class="card-grid">
    <div class="card">
        <div class="stat-label">Sales Today</div>
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
