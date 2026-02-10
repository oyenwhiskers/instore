@extends('layouts.app')

@section('page_title', 'Promoters')
@section('page_desc', 'Create and assign promoters under your company.')
@section('page_actions')
@endsection

@section('content')
<div class="card">
    <form method="GET" action="{{ route('customer.promoters.index') }}" class="filters-bar">
        <div class="filters-grid">
            <div class="form-group">
                <label>Promoter</label>
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="input" placeholder="Search name or promoter ID">
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="status" class="select">
                    <option value="">All statuses</option>
                    <option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option>
                    <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Inactive</option>
                </select>
            </div>
        </div>
        <div class="filters-actions">
            <div class="filters-buttons">
                <button type="submit" class="btn btn-primary">Apply</button>
                <a href="{{ route('customer.promoters.index') }}" class="btn btn-secondary">Reset</a>
            </div>
            <a href="{{ route('customer.promoters.create') }}" class="btn btn-primary">Add Promoter</a>
        </div>
    </form>
</div>

<div class="card card-tight">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Promoter ID</th>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th class="table-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($promoters as $promoter)
                    <tr>
                        <td>
                            @if ($promoter->promoter_id)
                                <div class="copy-field">
                                    <span>{{ $promoter->promoter_id }}</span>
                                    <button type="button" class="btn-icon" data-copy="{{ $promoter->promoter_id }}" data-copy-label="Copy" data-copied-label="Copied" aria-label="Copy promoter ID" title="Copy">
                                        <svg class="icon-copy" viewBox="0 0 24 24" width="14" height="14" aria-hidden="true" focusable="false">
                                            <path d="M9 9h10v12H9z" fill="currentColor" opacity="0.65" />
                                            <path d="M5 3h10v12H5z" fill="currentColor" />
                                        </svg>
                                        <svg class="icon-check" viewBox="0 0 24 24" width="14" height="14" aria-hidden="true" focusable="false">
                                            <path d="M9.5 16.2 4.8 11.5l1.4-1.4 3.3 3.3 8-8 1.4 1.4z" fill="currentColor" />
                                        </svg>
                                        <span class="copy-label">Copied</span>
                                    </button>
                                </div>
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $promoter->name }}</td>
                        <td>{{ $promoter->phone ?? '-' }}</td>
                        <td>{{ ucfirst($promoter->status) }}</td>
                        <td class="table-actions">
                            <div class="action-buttons">
                                <a href="{{ route('customer.promoters.edit', $promoter) }}" class="btn-icon" title="Edit" aria-label="Edit promoter">
                                    <svg viewBox="0 0 20 20" aria-hidden="true" focusable="false">
                                        <path d="M14.7 2.3a1 1 0 0 1 1.4 0l1.6 1.6a1 1 0 0 1 0 1.4l-9.9 9.9a1 1 0 0 1-.45.26l-3.7.9a.75.75 0 0 1-.9-.9l.9-3.7a1 1 0 0 1 .26-.45l9.9-9.9z" fill="currentColor"></path>
                                        <path d="M12.6 4.4l3 3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path>
                                    </svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="muted text-center">No promoters found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{ $promoters->links() }}
@endsection
