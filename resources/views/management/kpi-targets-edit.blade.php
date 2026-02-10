@extends('layouts.app')

@section('page_title', 'Set KPI Targets')
@section('page_desc', 'Define measurable targets for ' . $promoter->name . ' and reporting cadence.')
@section('page_actions')
    <a href="{{ route('management.kpi-targets.index') }}" class="btn btn-secondary">Back to KPI List</a>
@endsection

@section('content')
<div class="card">
    <form method="POST" action="{{ route('management.kpi-targets.update', $promoter) }}" class="form-section">
        @csrf
        @method('PUT')

        <div class="form-section">
            <div class="stat-label">Period Configuration</div>
            <div class="form-grid">
                <div class="form-group">
                    <label>Period Type</label>
                    <select name="period_type" class="select">
                        @php $periodType = old('period_type', $latestTarget?->period_type ?? 'daily'); @endphp
                        <option value="daily" @selected($periodType === 'daily')>Daily</option>
                        <option value="weekly" @selected($periodType === 'weekly')>Weekly</option>
                        <option value="monthly" @selected($periodType === 'monthly')>Monthly</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Period Start</label>
                    <input type="date" name="period_start" value="{{ old('period_start', $latestTarget?->period_start?->toDateString() ?? now()->toDateString()) }}" class="input" required>
                </div>
            </div>
        </div>

        <div class="form-section">
            <div class="stat-label">Target Metrics</div>
            <div class="form-grid">
                <div class="form-group">
                    <label>Sales Target (RM)</label>
                    <input type="number" step="0.01" min="0" name="target_sales_amount" value="{{ old('target_sales_amount', $latestTarget?->target_sales_amount ?? 0) }}" class="input" required>
                </div>
                <div class="form-group">
                    <label>Engagements Target</label>
                    <input type="number" min="0" name="target_engagements" value="{{ old('target_engagements', $latestTarget?->target_engagements ?? 0) }}" class="input" required>
                </div>
                <div class="form-group">
                    <label>Samplings Target</label>
                    <input type="number" min="0" name="target_samplings" value="{{ old('target_samplings', $latestTarget?->target_samplings ?? 0) }}" class="input" required>
                </div>
                <div class="form-group">
                    <label>Premium Tier 1 Target</label>
                    <input type="number" min="0" name="target_premium_tier1" value="{{ old('target_premium_tier1', $latestTarget?->target_premium_tier1 ?? 0) }}" class="input" required>
                </div>
                <div class="form-group">
                    <label>Premium Tier 2 Target</label>
                    <input type="number" min="0" name="target_premium_tier2" value="{{ old('target_premium_tier2', $latestTarget?->target_premium_tier2 ?? 0) }}" class="input" required>
                </div>
            </div>
        </div>

        <button class="btn btn-primary" type="submit">Save Targets</button>
    </form>
</div>
@endsection
