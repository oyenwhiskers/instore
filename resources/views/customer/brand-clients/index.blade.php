@extends('layouts.app')

@section('page_title', 'Brand Clients')
@section('page_desc', 'Manage the brands you represent and their activation status.')
@section('page_actions')
@endsection

@section('content')
<div class="card">
    <form method="GET" action="{{ route('customer.brand-clients.index') }}" class="filters-bar">
        <div class="filters-grid">
            <div class="form-group">
                <label>Search</label>
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="input" placeholder="Search brand name">
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="status" class="select">
                    <option value="">All statuses</option>
                    <option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option>
                    <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Inactive</option>
                </select>
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
                <a href="{{ route('customer.brand-clients.index') }}" class="btn btn-secondary">Reset</a>
            </div>
            <a href="{{ route('customer.brand-clients.create') }}" class="btn btn-primary">Add Brand</a>
        </div>
    </form>
</div>

<div class="card card-tight">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Status</th>
                    <th>Created At</th>
                    <th>Created By</th>
                    <th class="table-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($clients as $client)
                    <tr>
                        <td>{{ $client->name }}</td>
                        <td>{{ ucfirst($client->status) }}</td>
                        <td>{{ $client->created_at?->format('d M Y') ?? '-' }}</td>
                        <td>{{ $client->createdBy?->name ?? '-' }}</td>
                        <td class="table-actions">
                            <div class="action-buttons">
                                <a href="{{ route('customer.brand-clients.edit', $client) }}" class="btn-icon" title="Edit" aria-label="Edit brand client">
                                    <svg viewBox="0 0 20 20" aria-hidden="true" focusable="false">
                                        <path d="M14.7 2.3a1 1 0 0 1 1.4 0l1.6 1.6a1 1 0 0 1 0 1.4l-9.9 9.9a1 1 0 0 1-.45.26l-3.7.9a.75.75 0 0 1-.9-.9l.9-3.7a1 1 0 0 1 .26-.45l9.9-9.9z" fill="currentColor"></path>
                                        <path d="M12.6 4.4l3 3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path>
                                    </svg>
                                </a>
                                <button
                                    type="button"
                                    class="btn-icon"
                                    title="Delete"
                                    aria-label="Delete brand client"
                                    data-delete-button
                                    data-delete-action="{{ route('customer.brand-clients.destroy', $client) }}"
                                    data-entity-name="{{ $client->name }}"
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
                        <td colspan="5" class="muted text-center">No brand clients found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{ $clients->links() }}

<div class="modal-overlay" id="delete-brand-client-modal" aria-hidden="true">
    <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="delete-brand-client-title">
        <div class="modal-title" id="delete-brand-client-title">Delete brand client</div>
        <div class="modal-body">
            You are about to delete <span id="delete-brand-client-name">this brand client</span>. This action cannot be undone.
        </div>
        <div class="modal-actions">
            <button type="button" class="btn btn-secondary" data-modal-cancel>Cancel</button>
            <form method="POST" id="delete-brand-client-form">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-primary">Delete</button>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('delete-brand-client-modal');
        const nameEl = document.getElementById('delete-brand-client-name');
        const form = document.getElementById('delete-brand-client-form');
        const cancelBtn = modal?.querySelector('[data-modal-cancel]');

        const closeModal = () => {
            modal?.classList.remove('is-open');
            modal?.setAttribute('aria-hidden', 'true');
        };

        document.querySelectorAll('[data-delete-button]').forEach((button) => {
            button.addEventListener('click', () => {
                const action = button.getAttribute('data-delete-action');
                const name = button.getAttribute('data-entity-name') || 'this brand client';

                if (form && action) {
                    form.setAttribute('action', action);
                }

                if (nameEl) {
                    nameEl.textContent = name;
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
