@extends('layouts.app')

@section('page_title', 'Instore Workspace')
@section('page_desc', 'Enterprise performance tracking for field activations and KPI accountability.')
@section('page_actions')
    <a href="{{ route('login') }}" class="btn btn-secondary">Login</a>
@endsection

@section('content')
<div class="card-grid">
    <div class="card">
        <div class="stat-label">Workflow</div>
        <div class="stat-value">Promoter-first data capture</div>
        <p class="muted text-sm">Fast hourly reporting, premium validation, and location-linked accountability.</p>
    </div>
    <div class="card">
        <div class="stat-label">Visibility</div>
        <div class="stat-value">Management oversight</div>
        <p class="muted text-sm">Live KPI rollups, promoter activity, and compliance-ready audit trails.</p>
    </div>
    <div class="card">
        <div class="stat-label">Performance</div>
        <div class="stat-value">Actionable insights</div>
        <p class="muted text-sm">Daily targets, progress status, and drill-down reporting.</p>
    </div>
</div>

<div class="card">
    <div class="stat-label">Core Capabilities</div>
    <div class="form-grid">
        <div>Hourly reports with product mix</div>
        <div>Premium redemption validation by tier</div>
        <div>KPI targets, progress, and exceptions</div>
        <div>Role-based dashboards and filters</div>
    </div>
</div>
@endsection
