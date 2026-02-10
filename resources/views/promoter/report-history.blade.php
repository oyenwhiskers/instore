@extends('layouts.app')

@section('page_title', 'Submission History')
@section('page_desc', 'Review your hourly submissions with location and premium details.')
@section('page_actions')
    <a href="{{ route('promoter.reports.create') }}" class="btn btn-primary">New Report</a>
@endsection

@section('content')
<div class="card">
    <div class="table-responsive">
        <table class="table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Hour</th>
                <th>Location</th>
                <th>Sales (RM)</th>
                <th>Engagements</th>
                <th>Samplings</th>
                <th>Premiums</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($reports as $report)
                <tr>
                    <td>{{ $report->report_date->toDateString() }}</td>
                    <td>{{ str_pad($report->report_hour, 2, '0', STR_PAD_LEFT) }}:00</td>
                    <td>{{ $report->location?->name }}</td>
                    <td>{{ number_format($report->total_sales_amount, 2) }}</td>
                    <td>{{ $report->engagements_count }}</td>
                    <td>{{ $report->samplings_count }}</td>
                    <td>
                        @if ($report->premiums->isNotEmpty())
                            <div style="font-size: 13px;">
                                @foreach ($report->premiums->groupBy('premium_id') as $premiumId => $items)
                                    @php
                                        $premiumName = $items->first()?->premium?->gift_name ?? 'Premium';
                                        $premiumQty = $items->sum('quantity');
                                    @endphp
                                    <div style="margin-bottom: 2px;">
                                        {{ $premiumName }}:
                                        <span style="font-weight: 600;">{{ $premiumQty }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <span class="muted">-</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="muted">No reports yet.</td>
                </tr>
            @endforelse
        </tbody>
        </table>
    </div>
</div>

{{ $reports->links() }}
@endsection
