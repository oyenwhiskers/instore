@extends('layouts.app')

@section('page_title', 'Event Details')
@section('page_desc', 'Review the event setup and assignments.')
@section('page_actions')
    <a href="{{ route('customer.events.edit', $event) }}" class="btn btn-primary">Edit Event</a>
    <a href="{{ route('customer.events.index') }}" class="btn btn-secondary">Back</a>
@endsection

@section('content')
@php
    $promoterCount = $event->promoters->count();
    $productCount = $event->products->count();
    $premiumCount = $event->premiums->count();
    $activityItems = collect();
    $activityItems->push([
        'timestamp' => $event->created_at,
        'title' => 'Event created',
        'by' => $event->createdBy?->name,
        'details' => 'Status: ' . ucfirst($event->status),
    ]);

    if ($event->updated_at && $event->updated_at->ne($event->created_at)) {
        $activityItems->push([
            'timestamp' => $event->updated_at,
            'title' => 'Event updated',
            'by' => $event->updatedBy?->name,
            'details' => 'Status: ' . ucfirst($event->status),
        ]);
    }

    foreach ($stockMovements as $movement) {
        $activityItems->push([
            'timestamp' => $movement->created_at,
            'title' => $movement->movement_type === 'in' ? 'Stock in' : 'Stock out',
            'by' => $movement->createdBy?->name,
            'details' => ($movement->product?->name ?? 'Product') . ' x' . $movement->quantity,
        ]);
    }

    foreach ($promoterKpis as $kpi) {
        if (!$kpi->updated_at) {
            continue;
        }
        $activityItems->push([
            'timestamp' => $kpi->updated_at,
            'title' => 'KPI targets updated',
            'by' => $kpi->updatedBy?->name,
            'details' => $kpi->promoter?->name ?? 'Promoter',
        ]);
    }

    $activityItems = $activityItems
        ->filter(fn ($item) => (bool) $item['timestamp'])
        ->sortByDesc('timestamp');
    $activityCount = $activityItems->count();
@endphp

<div class="card event-header-card">
    <div class="event-header-grid">
        <div>
            <div class="stat-label">Event Name</div>
            <div class="stat-value">{{ $event->name }}</div>
        </div>
        <div>
            <div class="stat-label">Status</div>
            <div class="stat-value">
                <span class="status-badge status-{{ $event->status }}">{{ ucfirst($event->status) }}</span>
            </div>
        </div>
        <div>
            <div class="stat-label">Location</div>
            <div class="stat-value stat-value-wrap">{{ $event->location?->name ?? '-' }}</div>
        </div>
        <div>
            <div class="stat-label">Date Range</div>
            <div class="stat-value stat-value-inline">{{ $event->start_date?->format('d M Y') }} - {{ $event->end_date?->format('d M Y') }}</div>
        </div>
        <div>
            <div class="stat-label">Created By</div>
            <div class="stat-value stat-value-small">{{ $event->createdBy?->name ?? '-' }}</div>
        </div>
        <div>
            <div class="stat-label">Updated By</div>
            <div class="stat-value stat-value-small">{{ $event->updatedBy?->name ?? '-' }}</div>
        </div>
        <div>
            <div class="stat-label">Created At</div>
            <div class="stat-value stat-value-inline stat-value-small">{{ $event->created_at?->format('d M Y, H:i') ?? '-' }}</div>
        </div>
        <div>
            <div class="stat-label">Updated At</div>
            <div class="stat-value stat-value-inline stat-value-small">{{ $event->updated_at?->format('d M Y, H:i') ?? '-' }}</div>
        </div>
    </div>
    @if ($event->notes)
        <div class="form-group" style="margin-top: 16px;">
            <div class="stat-label">Notes</div>
            <div>{{ $event->notes }}</div>
        </div>
    @endif

    <div class="event-header-divider"></div>
    <div class="tabs tabs-inline" data-tabs data-tab-group="event-details" data-default-tab="promoters">
        <div class="tabs-nav" role="tablist">
            <button type="button" class="tab-button is-active" data-tab-target="promoters" role="tab" aria-selected="true">
                Promoters
                <span class="tab-badge">{{ $promoterCount }}</span>
            </button>
            <button type="button" class="tab-button" data-tab-target="kpi" role="tab" aria-selected="false">
                KPI Targets
                <span class="tab-badge">{{ $promoterCount }}</span>
            </button>
            <button type="button" class="tab-button" data-tab-target="products" role="tab" aria-selected="false">
                Products
                <span class="tab-badge">{{ $productCount }}</span>
            </button>
            <button type="button" class="tab-button" data-tab-target="premiums" role="tab" aria-selected="false">
                Premiums
                <span class="tab-badge">{{ $premiumCount }}</span>
            </button>
            <button type="button" class="tab-button" data-tab-target="activity" role="tab" aria-selected="false">
                Activity Log
                <span class="tab-badge">{{ $activityCount }}</span>
            </button>
        </div>
    </div>
</div>
<div class="tab-panel" data-tab-panel="promoters" data-tab-group="event-details">
    <div class="card">
        <form method="GET" action="{{ route('customer.events.show', $event) }}" class="filters-bar" style="margin-bottom: 12px;" data-auto-filter>
            <div class="filters-actions" style="width: 100%;">
                <div class="filters-grid" style="flex: 1;">
                    <div class="form-group">
                        <input type="text" name="attendance_search" value="{{ $attendanceFilters['search'] ?? '' }}" class="input" placeholder="Search promoter name">
                    </div>
                    <div class="form-group">
                        <select name="attendance_status" class="select">
                            <option value="">All status</option>
                            <option value="checked" @selected(($attendanceFilters['status'] ?? '') === 'checked')>Checked in</option>
                            <option value="missing" @selected(($attendanceFilters['status'] ?? '') === 'missing')>Not checked in</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <input type="date" name="attendance_date" value="{{ $attendanceFilters['date'] ?? '' }}" class="input">
                    </div>
                </div>
                <div class="tabs" data-tabs data-tab-group="promoter-views" data-default-tab="attendance">
                    <div class="tabs-nav" role="tablist">
                        <button type="button" class="tab-button is-active" data-tab-target="attendance" role="tab" aria-selected="true">Attendance</button>
                        <button type="button" class="tab-button" data-tab-target="schedule" role="tab" aria-selected="false">Schedule</button>
                    </div>
                </div>
            </div>
        </form>

        <div class="tab-panel" data-tab-panel="attendance" data-tab-group="promoter-views">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Status</th>
                            <th>Last Check-in</th>
                            <th class="table-actions">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($attendancePromoters as $promoter)
                            @php
                                $attendance = $attendanceByPromoter->get($promoter->id, [
                                    'count' => 0,
                                    'last_check_in' => null,
                                ]);
                                $hasCheckin = ($attendance['count'] ?? 0) > 0;
                                $latestCheckin = $latestCheckinsByPromoter->get($promoter->id);
                                $checkinImageUrl = $latestCheckin?->image_path
                                    ? \Illuminate\Support\Facades\Storage::url($latestCheckin->image_path)
                                    : null;
                            @endphp
                            <tr>
                                <td>{{ $promoter->name }}</td>
                                <td>
                                    <span class="attendance-badge {{ $hasCheckin ? 'is-checked' : 'is-missing' }}">
                                        {{ $hasCheckin ? 'Checked in' : 'Not checked in' }}
                                    </span>
                                </td>
                                <td>{{ $attendance['last_check_in']?->format('d M Y, H:i') ?? '-' }}</td>
                                <td class="table-actions">
                                    @if ($latestCheckin)
                                        <button type="button" class="btn-icon" data-checkin-view
                                            data-promoter-name="{{ $promoter->name }}"
                                            data-checkin-time="{{ $latestCheckin->check_in_at?->format('d M Y, H:i') ?? '-' }}"
                                            data-checkin-status="{{ $latestCheckin->status ?? '-' }}"
                                            data-checkin-lat="{{ $latestCheckin->latitude ?? '' }}"
                                            data-checkin-lng="{{ $latestCheckin->longitude ?? '' }}"
                                            data-checkin-image="{{ $checkinImageUrl ?? '' }}"
                                            title="View check-in" aria-label="View check-in">
                                            <svg viewBox="0 0 20 20" aria-hidden="true" focusable="false">
                                                <path d="M10 4c4.4 0 7.4 3.2 8.4 5-.9 1.8-4 5-8.4 5S2.6 10.8 1.6 9c1-1.8 4-5 8.4-5zm0 2.2A2.8 2.8 0 1 0 10 13.8 2.8 2.8 0 0 0 10 6.2zm0 1.6A1.2 1.2 0 1 1 10 10.2 1.2 1.2 0 0 1 10 7.8z" fill="currentColor"></path>
                                            </svg>
                                        </button>
                                    @else
                                        <span class="muted text-xs">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="muted">No promoters assigned.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="modal-overlay" id="checkin-modal" aria-hidden="true">
            <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="checkin-modal-title">
                <div class="modal-title" id="checkin-modal-title">Check-in details</div>
                <div class="modal-body">
                    <div class="text-sm" style="display: grid; gap: 6px;">
                        <div><strong>Promoter:</strong> <span id="checkin-modal-name">-</span></div>
                        <div><strong>Time:</strong> <span id="checkin-modal-time">-</span></div>
                        <div><strong>Status:</strong> <span id="checkin-modal-status">-</span></div>
                        <div><strong>Coordinates:</strong> <span id="checkin-modal-coords">-</span></div>
                    </div>
                    <div id="checkin-modal-image-wrap" style="margin-top: 12px;">
                        <img id="checkin-modal-image" class="checkin-image" src="" alt="Check-in image">
                        <div id="checkin-modal-image-empty" class="muted text-sm" style="display: none;">No image uploaded.</div>
                    </div>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" data-modal-cancel>Close</button>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const modal = document.getElementById('checkin-modal');
                const cancelBtn = modal?.querySelector('[data-modal-cancel]');
                const nameEl = document.getElementById('checkin-modal-name');
                const timeEl = document.getElementById('checkin-modal-time');
                const statusEl = document.getElementById('checkin-modal-status');
                const coordsEl = document.getElementById('checkin-modal-coords');
                const imageEl = document.getElementById('checkin-modal-image');
                const imageEmpty = document.getElementById('checkin-modal-image-empty');
                const imageWrap = document.getElementById('checkin-modal-image-wrap');

                const closeModal = () => {
                    modal?.classList.remove('is-open');
                    modal?.setAttribute('aria-hidden', 'true');
                };

                document.querySelectorAll('[data-checkin-view]').forEach((button) => {
                    button.addEventListener('click', () => {
                        if (nameEl) nameEl.textContent = button.getAttribute('data-promoter-name') || '-';
                        if (timeEl) timeEl.textContent = button.getAttribute('data-checkin-time') || '-';
                        if (statusEl) statusEl.textContent = button.getAttribute('data-checkin-status') || '-';
                        const lat = button.getAttribute('data-checkin-lat') || '';
                        const lng = button.getAttribute('data-checkin-lng') || '';
                        if (coordsEl) coordsEl.textContent = lat && lng ? `${lat}, ${lng}` : '-';

                        const imageUrl = button.getAttribute('data-checkin-image') || '';
                        if (imageEl && imageEmpty && imageWrap) {
                            if (imageUrl) {
                                imageEl.src = imageUrl;
                                imageEl.style.display = 'block';
                                imageEmpty.style.display = 'none';
                            } else {
                                imageEl.src = '';
                                imageEl.style.display = 'none';
                                imageEmpty.style.display = 'block';
                            }
                        }

                        modal?.classList.add('is-open');
                        modal?.setAttribute('aria-hidden', 'false');
                    });
                });

                cancelBtn?.addEventListener('click', closeModal);
                modal?.addEventListener('click', (event) => {
                    if (event.target === modal) {
                        closeModal();
                    }
                });
            });
        </script>

        <div class="tab-panel is-hidden" data-tab-panel="schedule" data-tab-group="promoter-views">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Start</th>
                            <th>End</th>
                            <th>Time</th>
                            <th class="table-actions">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($event->promoters as $promoter)
                            @php
                                $scheduleStartDate = $promoter->pivot?->start_date
                                    ? \Carbon\Carbon::parse($promoter->pivot->start_date)->format('Y-m-d')
                                    : '';
                                $scheduleEndDate = $promoter->pivot?->end_date
                                    ? \Carbon\Carbon::parse($promoter->pivot->end_date)->format('Y-m-d')
                                    : '';
                                $scheduleStartTime = $promoter->pivot?->start_time
                                    ? substr($promoter->pivot->start_time, 0, 5)
                                    : '';
                                $scheduleEndTime = $promoter->pivot?->end_time
                                    ? substr($promoter->pivot->end_time, 0, 5)
                                    : '';
                            @endphp
                            <tr>
                                <td>{{ $promoter->name }}</td>
                                <td>{{ $promoter->pivot?->start_date ? \Carbon\Carbon::parse($promoter->pivot->start_date)->format('d M Y') : '-' }}</td>
                                <td>{{ $promoter->pivot?->end_date ? \Carbon\Carbon::parse($promoter->pivot->end_date)->format('d M Y') : '-' }}</td>
                                <td>{{ $promoter->pivot?->start_time ?? '-' }} - {{ $promoter->pivot?->end_time ?? '-' }}</td>
                                <td class="table-actions">
                                    <button type="button" class="btn-icon" data-schedule-edit
                                        data-promoter-id="{{ $promoter->id }}"
                                        data-promoter-name="{{ $promoter->name }}"
                                        data-start-date="{{ $scheduleStartDate }}"
                                        data-end-date="{{ $scheduleEndDate }}"
                                        data-start-time="{{ $scheduleStartTime }}"
                                        data-end-time="{{ $scheduleEndTime }}"
                                        title="Edit schedule" aria-label="Edit schedule">
                                        <svg viewBox="0 0 20 20" aria-hidden="true" focusable="false">
                                            <path d="M14.7 2.3a1 1 0 0 1 1.4 0l1.6 1.6a1 1 0 0 1 0 1.4l-9.9 9.9a1 1 0 0 1-.45.26l-3.7.9a.75.75 0 0 1-.9-.9l.9-3.7a1 1 0 0 1 .26-.45l9.9-9.9z" fill="currentColor"></path>
                                            <path d="M12.6 4.4l3 3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="muted">No promoters assigned.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<div class="modal-overlay" id="schedule-modal" aria-hidden="true">
    <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="schedule-modal-title">
        <div class="modal-title" id="schedule-modal-title">Edit schedule</div>
        <form method="POST" action="{{ route('customer.events.schedule.update', $event) }}" id="schedule-form">
            @csrf
            @method('PUT')
            <input type="hidden" name="promoter_id" id="schedule-promoter-id">
            <div class="modal-body">
                <div class="text-sm" style="display: grid; gap: 10px;">
                    <div>
                        <strong>Promoter:</strong> <span id="schedule-promoter-name">-</span>
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Start date</label>
                            <input type="date" name="start_date" id="schedule-start-date" class="input">
                        </div>
                        <div class="form-group">
                            <label>End date</label>
                            <input type="date" name="end_date" id="schedule-end-date" class="input">
                        </div>
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Start time</label>
                            <input type="time" name="start_time" id="schedule-start-time" class="input">
                        </div>
                        <div class="form-group">
                            <label>End time</label>
                            <input type="time" name="end_time" id="schedule-end-time" class="input">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-actions modal-actions-split">
                <button type="button" class="btn btn-secondary" data-modal-cancel>Cancel</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('schedule-modal');
        const cancelBtn = modal?.querySelector('[data-modal-cancel]');
        const promoterIdEl = document.getElementById('schedule-promoter-id');
        const promoterNameEl = document.getElementById('schedule-promoter-name');
        const startDateEl = document.getElementById('schedule-start-date');
        const endDateEl = document.getElementById('schedule-end-date');
        const startTimeEl = document.getElementById('schedule-start-time');
        const endTimeEl = document.getElementById('schedule-end-time');

        const closeModal = () => {
            modal?.classList.remove('is-open');
            modal?.setAttribute('aria-hidden', 'true');
        };

        document.querySelectorAll('[data-schedule-edit]').forEach((button) => {
            button.addEventListener('click', () => {
                if (promoterIdEl) promoterIdEl.value = button.getAttribute('data-promoter-id') || '';
                if (promoterNameEl) promoterNameEl.textContent = button.getAttribute('data-promoter-name') || '-';
                if (startDateEl) startDateEl.value = button.getAttribute('data-start-date') || '';
                if (endDateEl) endDateEl.value = button.getAttribute('data-end-date') || '';
                if (startTimeEl) startTimeEl.value = button.getAttribute('data-start-time') || '';
                if (endTimeEl) endTimeEl.value = button.getAttribute('data-end-time') || '';

                modal?.classList.add('is-open');
                modal?.setAttribute('aria-hidden', 'false');
            });
        });

        cancelBtn?.addEventListener('click', closeModal);
        modal?.addEventListener('click', (event) => {
            if (event.target === modal) {
                closeModal();
            }
        });
    });
</script>

<div class="tab-panel is-hidden" data-tab-panel="kpi" data-tab-group="event-details">
        <div class="card">
            <div class="stat-label">Promoter KPI Targets</div>
            @if ($event->promoters->isEmpty())
                <div class="muted">No promoters assigned.</div>
            @else
                <form method="POST" action="{{ route('customer.events.kpis.update', $event) }}">
                    @csrf
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th class="text-center">Promoter</th>
                                    <th class="text-center">Sales Target</th>
                                    <th class="text-center">Engagements</th>
                                    <th class="text-center">Samplings</th>
                                    @foreach ($event->premiums as $premium)
                                        <th class="text-center">{{ $premium->gift_name }}</th>
                                    @endforeach
                                    <th class="text-center">Updated By</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($event->promoters as $promoter)
                                    @php
                                        $kpi = $promoterKpis->get($promoter->id);
                                        $promoterPremiumTargets = $premiumTargets->get($promoter->id, collect());
                                    @endphp
                                    <tr>
                                        <td>{{ $promoter->name }}</td>
                                        <td class="text-right">
                                            <input type="number" name="kpis[{{ $promoter->id }}][target_sales_amount]" value="{{ old('kpis.' . $promoter->id . '.target_sales_amount', $kpi?->target_sales_amount) }}" class="input text-right" step="0.01" min="0">
                                        </td>
                                        <td class="text-right">
                                            <input type="number" name="kpis[{{ $promoter->id }}][target_engagements]" value="{{ old('kpis.' . $promoter->id . '.target_engagements', $kpi?->target_engagements) }}" class="input text-right" step="1" min="0">
                                        </td>
                                        <td class="text-right">
                                            <input type="number" name="kpis[{{ $promoter->id }}][target_samplings]" value="{{ old('kpis.' . $promoter->id . '.target_samplings', $kpi?->target_samplings) }}" class="input text-right" step="1" min="0">
                                        </td>
                                        @foreach ($event->premiums as $premium)
                                            @php
                                                $premiumTarget = $promoterPremiumTargets->get($premium->id);
                                            @endphp
                                                <td class="text-right">
                                                <input type="number"
                                                    name="premium_targets[{{ $promoter->id }}][{{ $premium->id }}]"
                                                    value="{{ old('premium_targets.' . $promoter->id . '.' . $premium->id, $premiumTarget?->target_qty ?? 0) }}"
                                                    class="input text-right" step="1" min="0">
                                            </td>
                                        @endforeach
                                        <td>{{ $kpi?->updatedBy?->name ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="filters-actions" style="margin-top: 16px;">
                        <button class="btn btn-primary" type="submit">Save KPI Targets</button>
                    </div>
                </form>
            @endif
        </div>
    </div>

<div class="tab-panel is-hidden" data-tab-panel="products" data-tab-group="event-details">
        <div class="card">
            <div class="filters-actions" style="margin-bottom: 12px;">
                <div class="stat-label">Stock &amp; Products</div>
                <div class="tabs" data-tabs data-tab-group="product-views" data-default-tab="movements">
                    <div class="tabs-nav" role="tablist">
                        <button type="button" class="tab-button is-active" data-tab-target="movements" role="tab" aria-selected="true">Stock Movements</button>
                        <button type="button" class="tab-button" data-tab-target="manage" role="tab" aria-selected="false">Manage Products</button>
                    </div>
                </div>
            </div>
            @if ($event->products->isEmpty())
                <div class="muted">No products linked to this event.</div>
            @else
                <div class="tab-panel" data-tab-panel="movements" data-tab-group="product-views">
                    <form method="GET" action="{{ route('customer.events.show', $event) }}" class="filters-bar" style="margin-bottom: 16px;" data-auto-filter>
                        <div class="filters-grid">
                            <div class="form-group">
                                <input type="text" name="stock_search" value="{{ $stockFilters['search'] ?? '' }}" class="input" placeholder="Search product name">
                            </div>
                            <div class="form-group">
                                <select name="stock_type" class="select">
                                    <option value="">All types</option>
                                    <option value="in" @selected(($stockFilters['type'] ?? '') === 'in')>Stock In</option>
                                    <option value="out" @selected(($stockFilters['type'] ?? '') === 'out')>Stock Out</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <input type="date" name="stock_date" value="{{ $stockFilters['date'] ?? '' }}" class="input">
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Product</th>
                                    <th>Type</th>
                                    <th class="text-right">Qty</th>
                                    <th>By</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($stockMovements as $movement)
                                    <tr>
                                        <td>{{ $movement->created_at?->format('d M Y, H:i') ?? '-' }}</td>
                                        <td>{{ $movement->product?->name ?? '-' }}</td>
                                        <td>{{ $movement->movement_type === 'in' ? 'Stock In' : 'Stock Out' }}</td>
                                        <td class="text-right">{{ $movement->quantity }}</td>
                                        <td>{{ $movement->createdBy?->name ?? '-' }}</td>
                                        <td>{{ $movement->notes ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="muted">No stock movements yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="tab-panel is-hidden" data-tab-panel="manage" data-tab-group="product-views">
                    <form method="POST" action="{{ route('customer.events.stock-balances.update', $event) }}">
                        @csrf
                        <div class="muted text-sm" style="margin-bottom: 12px;">
                            Add or reduce quantity to create stock movements. Price is used for KPI sales targets.
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th class="text-right">Current Balance</th>
                                        <th class="text-right">Adjust Qty</th>
                                        <th class="text-right">Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($event->products as $product)
                                        <tr>
                                            <td>{{ $product->name }}</td>
                                            <td class="text-right">{{ $stockBalances[$product->id] ?? 0 }}</td>
                                            <td class="text-right">
                                                <input type="number" name="adjustments[{{ $product->id }}]"
                                                    value="{{ old('adjustments.' . $product->id) }}"
                                                    class="input text-right" step="1" placeholder="0">
                                            </td>
                                            <td class="text-right">
                                                <input type="number" name="prices[{{ $product->id }}]"
                                                    value="{{ old('prices.' . $product->id, $product->pivot?->unit_price) }}"
                                                    class="input text-right" min="0" step="0.01" placeholder="0.00">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="filters-actions" style="margin-top: 16px;">
                            <button class="btn btn-primary" type="submit">Save Changes</button>
                        </div>
                    </form>
                </div>
            @endif
        </div>
    </div>

<div class="tab-panel is-hidden" data-tab-panel="premiums" data-tab-group="event-details">
        <div class="card">
            <div class="stat-label">Event Premiums</div>
            @if ($event->premiums->isEmpty())
                <div class="muted">No premiums linked to this event.</div>
            @else
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Gift</th>
                                <th>Note</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($event->premiums as $premium)
                                <tr>
                                    <td>{{ $premium->gift_name }}</td>
                                    <td>{{ $premium->mechanic_description }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
</div>

<div class="tab-panel is-hidden" data-tab-panel="activity" data-tab-group="event-details">
        <div class="card">
            <div class="stat-label">Activity Log</div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Activity</th>
                            <th>By</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($activityItems as $item)
                            <tr>
                                <td>{{ $item['timestamp']?->format('d M Y, H:i') ?? '-' }}</td>
                                <td>{{ $item['title'] }}</td>
                                <td>{{ $item['by'] ?? '-' }}</td>
                                <td>{{ $item['details'] ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="muted">No activity yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
</div>
@endsection
