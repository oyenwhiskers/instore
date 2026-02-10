@extends('layouts.app')

@section('page_title', 'Units')
@section('page_desc', 'Create and manage measurement units for products.')
@section('page_actions')
@endsection

@section('content')
<div class="card">
    <form method="GET" action="{{ route('customer.units.index') }}" class="filters-bar">
        <div class="filters-grid">
            <div class="form-group">
                <label>Search</label>
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="input" placeholder="Search unit name">
            </div>
            <div class="form-group">
                <label>Created From</label>
                <input type="date" name="created_from" value="{{ $filters['created_from'] ?? '' }}" class="input">
            </div>
            <div class="form-group">
                <label>Created To</label>
                <input type="date" name="created_to" value="{{ $filters['created_to'] ?? '' }}" class="input">
            </div>
        </div>
        <div class="filters-actions">
            <div class="filters-buttons">
                <button type="submit" class="btn btn-primary">Apply</button>
                <a href="{{ route('customer.units.index') }}" class="btn btn-secondary">Reset</a>
            </div>
            <a href="{{ route('customer.units.create') }}" class="btn btn-primary">Add Unit</a>
        </div>
    </form>
</div>

<div class="card card-tight">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Created At</th>
                    <th>Created By</th>
                    <th class="table-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($units as $unit)
                    <tr>
                        <td>{{ $unit->name }}</td>
                        <td>{{ $unit->created_at?->format('d M Y') ?? '-' }}</td>
                        <td>{{ $unit->createdBy?->name ?? '-' }}</td>
                        <td class="table-actions">
                            <div class="action-buttons">
                                <a href="{{ route('customer.units.edit', $unit) }}" class="btn-icon" title="Edit" aria-label="Edit unit">
                                    <svg viewBox="0 0 20 20" aria-hidden="true" focusable="false">
                                        <path d="M14.7 2.3a1 1 0 0 1 1.4 0l1.6 1.6a1 1 0 0 1 0 1.4l-9.9 9.9a1 1 0 0 1-.45.26l-3.7.9a.75.75 0 0 1-.9-.9l.9-3.7a1 1 0 0 1 .26-.45l9.9-9.9z" fill="currentColor"></path>
                                        <path d="M12.6 4.4l3 3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path>
                                    </svg>
                                </a>
                                <button
                                    type="button"
                                    class="btn-icon"
                                    title="Delete"
                                    aria-label="Delete unit"
                                    data-delete-button
                                    data-delete-action="{{ route('customer.units.destroy', $unit) }}"
                                    data-unit-name="{{ $unit->name }}"
                                >
                                        <svg viewBox="0 0 20 20" aria-hidden="true" focusable="false">
                                            <path d="M7 2h6l1 2h3v2H3V4h3l1-2z" fill="currentColor"></path>
                                            <path d="M6 7h8l-.7 9.5a1 1 0 0 1-1 .9H7.7a1 1 0 0 1-1-.9L6 7z" fill="currentColor"></path>
                                            <path d="M8.2 9.2v6" stroke="#ffffff" stroke-width="1.4" stroke-linecap="round"></path>
                                            <path d="M11.8 9.2v6" stroke="#ffffff" stroke-width="1.4" stroke-linecap="round"></path>
                                        </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="muted text-center">No units found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{ $units->links() }}

<div class="modal-overlay" id="delete-unit-modal" aria-hidden="true">
    <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="delete-unit-title">
        <div class="modal-title" id="delete-unit-title">Delete unit</div>
        <div class="modal-body">
            You are about to delete <span id="delete-unit-name">this unit</span>. This action cannot be undone.
        </div>
        <div class="modal-actions">
            <button type="button" class="btn btn-secondary" data-modal-cancel>Cancel</button>
            <form method="POST" id="delete-unit-form">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-primary">Delete</button>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('delete-unit-modal');
        const nameEl = document.getElementById('delete-unit-name');
        const form = document.getElementById('delete-unit-form');
        const cancelBtn = modal?.querySelector('[data-modal-cancel]');

        const closeModal = () => {
            modal?.classList.remove('is-open');
            modal?.setAttribute('aria-hidden', 'true');
        };

        document.querySelectorAll('[data-delete-button]').forEach((button) => {
            button.addEventListener('click', () => {
                const action = button.getAttribute('data-delete-action');
                const unitName = button.getAttribute('data-unit-name') || 'this unit';

                if (form && action) {
                    form.setAttribute('action', action);
                }

                if (nameEl) {
                    nameEl.textContent = unitName;
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
@endsection
