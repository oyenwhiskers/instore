@extends('layouts.app')

@section('page_title', 'Submit Hourly Report')
@section('page_desc', 'Capture your field activity with validated sales and event premiums.')
@section('page_actions')
    <a href="{{ route('promoter.reports.history') }}" class="btn btn-secondary">View History</a>
@endsection

@section('content')
<div class="card">
    <form method="POST" action="{{ route('promoter.reports.store') }}" class="form-section">
        @csrf
        <div class="form-section">
            <div class="stat-label">Assignment Context</div>
            @if ($assignment)
                <div class="form-grid text-sm">
                    <div><strong>Outlet:</strong> {{ $assignment->location?->name }}</div>
                    <div><strong>Duration:</strong>
                        {{ $assignment->start_date?->toDateString() ?? 'TBD' }}
                        - {{ $assignment->end_date?->toDateString() ?? 'TBD' }}
                    </div>
                    <div><strong>Shift:</strong> {{ $assignment->start_time ?? 'TBD' }} - {{ $assignment->end_time ?? 'TBD' }}</div>
                </div>
            @else
                <p class="muted text-sm">No assignment found. Contact management if this is incorrect.</p>
            @endif
        </div>

        <div class="form-section">
            <div class="stat-label">Report Context</div>
            <div class="form-grid">
                <div class="form-group">
                    <label>Report Date</label>
                    <input type="date" name="report_date" value="{{ old('report_date', now()->toDateString()) }}" class="input" required>
                </div>
                <div class="form-group">
                    <label>Report Hour</label>
                    <select name="report_hour" class="select" required>
                        @for ($i = 0; $i <= 23; $i++)
                            <option value="{{ $i }}" @selected(old('report_hour') == $i)>{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}:00</option>
                        @endfor
                    </select>
                </div>
                <div class="form-group">
                    <label>Assigned Location</label>
                    <input type="text" value="{{ $profile?->location?->name ?? 'Not assigned' }}" class="input" disabled>
                </div>
            </div>
        </div>

        <div class="form-section">
            <div class="stat-label">Performance Metrics</div>
            <div class="form-grid">
                <div class="form-group">
                    <label>Total Sales (RM)</label>
                    <input type="number" step="0.01" min="0" name="total_sales_amount" value="{{ old('total_sales_amount', 0) }}" class="input" required>
                </div>
                <div class="form-group">
                    <label>Engagements</label>
                    <input type="number" min="0" name="engagements_count" value="{{ old('engagements_count', 0) }}" class="input" required>
                </div>
                <div class="form-group">
                    <label>Samplings</label>
                    <input type="number" min="0" name="samplings_count" value="{{ old('samplings_count', 0) }}" class="input" required>
                </div>
            </div>
        </div>

        <div class="form-section">
            <div class="stat-label">Product Sales</div>
            <div class="form-grid">
                @foreach ($products as $product)
                    <div class="form-group">
                        <label>{{ $product->name }}</label>
                        <input type="number" min="0" name="items[{{ $product->id }}]" value="{{ old('items.' . $product->id, 0) }}" class="input">
                    </div>
                @endforeach
            </div>
        </div>

        <div class="form-section">
            <div class="stat-label">Premium Redemptions</div>
            @if ($eventPremiums->isNotEmpty())
                <div class="form-grid">
                    @foreach ($eventPremiums as $premium)
                        <div class="form-group">
                            <label>{{ $premium->gift_name }}</label>
                            <input type="number" min="0" name="premiums[{{ $premium->id }}]" value="{{ old('premiums.' . $premium->id, 0) }}" class="input">
                        </div>
                    @endforeach
                </div>
            @else
                <p class="muted text-sm">No premiums linked to this event.</p>
            @endif
        </div>

        <button class="btn btn-primary" type="submit">Submit Report</button>
    </form>
</div>
@endsection
