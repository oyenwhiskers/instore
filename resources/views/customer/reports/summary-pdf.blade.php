<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Event Sales Summary</title>
    <style>
        body { font-family: "Inter", Arial, sans-serif; color: #0f172a; margin: 24px; }
        h1 { font-size: 20px; margin-bottom: 4px; }
        h2 { font-size: 16px; margin: 16px 0 8px; }
        h3 { font-size: 14px; margin: 12px 0 6px; }
        .muted { color: #64748b; }
        .summary { margin-bottom: 16px; }
        .badge { display: inline-block; padding: 3px 8px; border-radius: 999px; font-size: 11px; font-weight: 600; }
        .above { background: #dcfce7; color: #166534; }
        .on_track { background: #fef9c3; color: #92400e; }
        .below { background: #fee2e2; color: #991b1b; }
        .active { background: #dcfce7; color: #166534; }
        .upcoming { background: #dbeafe; color: #1e3a8a; }
        .completed { background: #ccfbf1; color: #0f766e; }
        table { width: 100%; border-collapse: collapse; font-size: 12px; margin-bottom: 16px; }
        th, td { border: 1px solid #e2e8f0; padding: 6px 8px; vertical-align: top; }
        th { background: #f8fafc; text-align: left; }
        ul { margin: 0; padding-left: 16px; }
        @media print { body { margin: 0; } }
    </style>
</head>
<body>
    <h1>Event Sales Summary</h1>
    <div class="summary muted">
        Generated {{ now()->format('d M Y H:i') }}
        @if (!empty($filters['date'])) • Date filter: {{ $filters['date'] }} @endif
        @if (!empty($filters['status'])) • Status: {{ ucfirst($filters['status']) }} @endif
    </div>

    @php
        $eventGroups = $summaries->groupBy(fn($item) => $item['event']->id);
    @endphp

    @foreach ($eventGroups as $eventSummaries)
        @php
            $event = $eventSummaries->first()['event'];
            $eventStatus = $eventSummaries->first()['event_status'] ?? 'completed';
            $brandGroups = $eventSummaries->groupBy('brand');
        @endphp

        <h2>{{ $event->name }}</h2>
        <div class="muted">{{ $event->location?->name ?? '-' }} • {{ $event->start_date->format('d M') }} - {{ $event->end_date->format('d M Y') }}</div>
        <div class="muted">{{ $event->promoters->count() }} promoters • {{ $event->products->count() }} products • {{ $event->premiums->count() }} premiums</div>
        <div style="margin-top: 6px;">
            <span class="badge {{ $eventStatus }}">{{ ucfirst($eventStatus) }}</span>
        </div>

        @foreach ($brandGroups as $brandName => $brandSummaries)
            <h3>{{ $brandName }}</h3>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>KPI Targets</th>
                        <th>Actual Performance</th>
                        <th>Status</th>
                        <th>Notes</th>
                        <th>Insight</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($brandSummaries->sortBy('date') as $summary)
                        @php
                            $targets = $summary['targets'];
                            $actuals = $summary['actuals'];
                            $status = $summary['status'];
                            $badgeClass = $status['overall'];
                        @endphp
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($summary['date'])->format('d M Y') }}</td>
                            <td>
                                Sales: RM {{ number_format($targets['sales'], 2) }}<br>
                                Engagements: {{ number_format($targets['engagements']) }}<br>
                                Samplings: {{ number_format($targets['samplings']) }}<br>
                                Redemptions: {{ number_format($targets['redemptions']) }}<br>
                                Conversion: {{ number_format($targets['conversion'], 2) }}
                            </td>
                            <td>
                                Sales: RM {{ number_format($actuals['sales'], 2) }}<br>
                                Engagements: {{ number_format($actuals['engagements']) }}<br>
                                Samplings: {{ number_format($actuals['samplings']) }}<br>
                                Redemptions: {{ number_format($actuals['redemptions']) }}<br>
                                Conversion: {{ number_format($actuals['conversion'], 2) }}
                            </td>
                            <td><span class="badge {{ $badgeClass }}">{{ str_replace('_', ' ', ucfirst($status['overall'])) }}</span></td>
                            <td>
                                @if (!empty($summary['notes']))
                                    <ul>
                                        @foreach ($summary['notes'] as $note)
                                            <li>{{ $note }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    No issues flagged.
                                @endif
                            </td>
                            <td>{{ $summary['insight'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endforeach
    @endforeach
</body>
</html>
