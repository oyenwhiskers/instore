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
                @php
                    $tier1 = $report->premiums->where('tier', 1)->sum('quantity');
                    $tier2 = $report->premiums->where('tier', 2)->sum('quantity');
                @endphp
                <tr>
                    <td>{{ $report->report_date->toDateString() }}</td>
                    <td>{{ str_pad($report->report_hour, 2, '0', STR_PAD_LEFT) }}:00</td>
                    <td>{{ $report->location?->name }}</td>
                    <td>{{ number_format($report->total_sales_amount, 2) }}</td>
                    <td>{{ $report->engagements_count }}</td>
                    <td>{{ $report->samplings_count }}</td>
                    <td>T1 {{ $tier1 }} / T2 {{ $tier2 }}</td>
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
