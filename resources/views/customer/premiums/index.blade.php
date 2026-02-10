@extends('layouts.app')

@section('page_title', 'Premiums')
@section('page_desc', 'Manage gifts and simple notes for premium redemptions.')
@section('page_actions')
@endsection

@section('content')
<div class="card">
    <form method="GET" action="{{ route('customer.premiums.index') }}" class="filters-bar">
        <div class="filters-grid">
            <div class="form-group">
                <label>Search</label>
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="input" placeholder="Search gift or note">
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
                <a href="{{ route('customer.premiums.index') }}" class="btn btn-secondary">Reset</a>
            </div>
            <a href="{{ route('customer.premiums.create') }}" class="btn btn-primary">Add Premium</a>
        </div>
    </form>
</div>

<div class="card card-tight">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Gift</th>
                    <th>Note</th>
                    <th>Created At</th>
                    <th>Created By</th>
                    <th class="table-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($premiums as $premium)
                    <tr>
                        <td>{{ $premium->gift_name }}</td>
                        <td>{{ $premium->mechanic_description }}</td>
                        <td>{{ $premium->created_at?->format('d M Y') ?? '-' }}</td>
                        <td>{{ $premium->createdBy?->name ?? '-' }}</td>
                        <td class="table-actions">
                            <div class="action-buttons">
                                <a href="{{ route('customer.premiums.edit', $premium) }}" class="btn-icon" title="Edit" aria-label="Edit premium">
                                    <svg viewBox="0 0 20 20" aria-hidden="true" focusable="false">
                                        <path d="M14.7 2.3a1 1 0 0 1 1.4 0l1.6 1.6a1 1 0 0 1 0 1.4l-9.9 9.9a1 1 0 0 1-.45.26l-3.7.9a.75.75 0 0 1-.9-.9l.9-3.7a1 1 0 0 1 .26-.45l9.9-9.9z" fill="currentColor"></path>
                                        <path d="M12.6 4.4l3 3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path>
                                    </svg>
                                </a>
                                <button
                                    type="button"
                                    class="btn-icon"
                                    title="Delete"
                                    aria-label="Delete premium"
                                    data-delete-button
                                    data-delete-action="{{ route('customer.premiums.destroy', $premium) }}"
                                    data-premium-name="{{ $premium->gift_name }}"
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
                        <td colspan="5" class="muted text-center">No premiums found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{ $premiums->links() }}

<div class="modal-overlay" id="delete-premium-modal" aria-hidden="true">
    <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="delete-premium-title">
        <div class="modal-title" id="delete-premium-title">Delete premium</div>
        <div class="modal-body">
            You are about to delete <span id="delete-premium-name">this premium</span>. This action cannot be undone.
        </div>
        <div class="modal-actions">
            <button type="button" class="btn btn-secondary" data-modal-cancel>Cancel</button>
            <form method="POST" id="delete-premium-form">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-primary">Delete</button>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('delete-premium-modal');
        const nameEl = document.getElementById('delete-premium-name');
        const form = document.getElementById('delete-premium-form');
        const cancelBtn = modal?.querySelector('[data-modal-cancel]');

        const closeModal = () => {
            modal?.classList.remove('is-open');
            modal?.setAttribute('aria-hidden', 'true');
        };

        document.querySelectorAll('[data-delete-button]').forEach((button) => {
            button.addEventListener('click', () => {
                const action = button.getAttribute('data-delete-action');
                const premiumName = button.getAttribute('data-premium-name') || 'this premium';

                if (form && action) {
                    form.setAttribute('action', action);
                }

                if (nameEl) {
                    nameEl.textContent = premiumName;
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
