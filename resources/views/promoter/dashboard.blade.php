@extends('layouts.app')

@section('page_title', 'Promoter Dashboard')
@section('page_desc', 'Track hourly performance, premium redemptions, and KPI progress.')
@section('page_actions')
    <a href="{{ route('promoter.reports.create') }}" class="btn btn-primary">Submit Report</a>
@endsection

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
        <div class="stat-value">{{ $premiumTotal }}</div>
    </div>
</div>

<div class="split-view">
    <div class="card">
        <div class="stat-label">Assignment Overview</div>
        @if ($assignment)
            <div class="form-grid text-sm">
                <div><strong>Outlet:</strong> {{ $assignment->location?->name }}</div>
                <div><strong>Duration:</strong>
                    {{ $assignment->start_date?->toDateString() ?? 'TBD' }}
                    - {{ $assignment->end_date?->toDateString() ?? 'TBD' }}
                </div>
                <div><strong>Shift:</strong>
                    {{ $assignment->start_time ?? 'TBD' }} - {{ $assignment->end_time ?? 'TBD' }}
                </div>
                <div><strong>Notes:</strong> {{ $assignment->notes ?? 'No additional notes' }}</div>
            </div>
        @else
            <p class="muted text-sm">No assignment found. Contact management.</p>
        @endif

        <div class="form-section" style="margin-top: 16px;">
            <div class="stat-label">Check-In Attendance</div>
            <p class="muted text-sm">Confirm presence at your assigned outlet. Location will be captured.</p>
            <form method="POST" action="{{ route('promoter.checkins.store') }}" id="checkin-form" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="latitude" id="checkin-lat">
                <input type="hidden" name="longitude" id="checkin-lng">
                <div class="form-group" style="margin-bottom: 10px;">
                    <input type="file" name="image" class="input" accept="image/*">
                </div>
                <button class="btn btn-primary btn-block" type="submit">Check In Now</button>
            </form>
            @if ($lastCheckin)
                <p class="text-xs muted">Last check-in: {{ $lastCheckin->check_in_at->format('Y-m-d H:i') }}</p>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="stat-label">Products To Promote</div>
        @if ($assignedProducts->isNotEmpty())
            <div class="form-grid text-sm">
                @foreach ($assignedProducts as $product)
                    <div>{{ $product->name }}</div>
                @endforeach
            </div>
        @else
            <p class="muted text-sm">No products assigned yet.</p>
        @endif

        <div class="form-section" style="margin-top: 16px;">
            <div class="stat-label">Latest KPI Targets</div>
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
    </div>
</div>

<script>
    const checkinForm = document.getElementById('checkin-form');
    if (checkinForm && navigator.geolocation) {
        checkinForm.addEventListener('submit', function (event) {
            event.preventDefault();
            navigator.geolocation.getCurrentPosition(function (position) {
                const lat = document.getElementById('checkin-lat');
                const lng = document.getElementById('checkin-lng');
                if (lat && lng) {
                    lat.value = position.coords.latitude;
                    lng.value = position.coords.longitude;
                }
                checkinForm.submit();
            }, function () {
                checkinForm.submit();
            }, {
                enableHighAccuracy: true,
                timeout: 8000,
            });
        });
    }
</script>
@endsection
