@extends('layouts.app')

@section('page_title', 'Management Dashboard')
@section('page_desc', 'Daily performance pulse with promoter and location coverage.')

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

<div class="card-grid">
    <div class="card">
        <div class="stat-label">Promoters Active</div>
        <div class="stat-value">{{ $promoterCount }}</div>
    </div>
    <div class="card">
        <div class="stat-label">Locations Managed</div>
        <div class="stat-value">{{ $locationCount }}</div>
    </div>
</div>
@endsection
