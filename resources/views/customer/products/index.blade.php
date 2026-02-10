@extends('layouts.app')

@section('page_title', 'Products')
@section('page_desc', 'Maintain the product list for each brand client.')
@section('page_actions')
@endsection

@section('content')
@php
    $currentView = $viewMode ?? 'table';
    $tableUrl = request()->fullUrlWithQuery(['view' => 'table']);
    $groupUrl = request()->fullUrlWithQuery(['view' => 'group']);
    $groupedProducts = $products->getCollection()->groupBy(fn ($product) => $product->brandClient?->name ?? 'Unassigned');
@endphp
<div class="card">
    <form method="GET" action="{{ route('customer.products.index') }}" class="filters-bar">
        <div class="filters-grid">
            <div class="form-group">
                <label>Search</label>
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="input" placeholder="Search product name">
            </div>
            <div class="form-group">
                <label>Brand</label>
                <select name="brand_client_id" class="select">
                    <option value="">All brands</option>
                    @foreach ($brandClients as $client)
                        <option value="{{ $client->id }}" @selected(($filters['brand_client_id'] ?? '') == $client->id)>{{ $client->name }}</option>
                    @endforeach
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
                <a href="{{ route('customer.products.index') }}" class="btn btn-secondary">Reset</a>
            </div>
            <div class="view-toggle">
                <a href="{{ $tableUrl }}" data-view-link data-view="table" class="btn {{ $currentView === 'table' ? 'btn-primary' : 'btn-secondary' }}">
                    <svg viewBox="0 0 20 20" aria-hidden="true" focusable="false">
                        <rect x="3" y="4" width="5" height="5" fill="currentColor"></rect>
                        <rect x="12" y="4" width="5" height="5" fill="currentColor"></rect>
                        <rect x="3" y="11" width="5" height="5" fill="currentColor"></rect>
                        <rect x="12" y="11" width="5" height="5" fill="currentColor"></rect>
                    </svg>
                    Table View
                </a>
                <a href="{{ $groupUrl }}" data-view-link data-view="group" class="btn {{ $currentView === 'group' ? 'btn-primary' : 'btn-secondary' }}">
                    <svg viewBox="0 0 20 20" aria-hidden="true" focusable="false">
                        <rect x="3" y="4" width="14" height="2" fill="currentColor"></rect>
                        <rect x="3" y="9" width="10" height="2" fill="currentColor"></rect>
                        <rect x="3" y="14" width="6" height="2" fill="currentColor"></rect>
                    </svg>
                    Grouped by Brand
                </a>
                <a href="{{ route('customer.products.create') }}" class="btn btn-primary">Add Product</a>
            </div>
        </div>
    </form>
</div>

<div data-view-panel="group" class="{{ $currentView === 'group' ? '' : 'is-hidden' }}">
    <div class="product-group-list">
        @forelse ($groupedProducts as $brandName => $items)
            <details class="product-group">
                <summary>
                    <span class="product-group-title">{{ $brandName }}</span>
                    <span class="product-group-meta">
                        <span class="product-group-count">{{ $items->count() }} products</span>
                        <span class="product-group-arrow" aria-hidden="true"></span>
                    </span>
                </summary>
                <div class="product-group-body">
                    <div class="table-responsive">
                        <table class="table table-compact">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>SKU</th>
                                    <th>Unit</th>
                                    <th>Status</th>
                                    <th class="table-actions">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($items as $product)
                                    <tr>
                                        <td>{{ $product->name }}</td>
                                        <td>{{ $product->sku ?? '-' }}</td>
                                        <td>{{ $product->unit?->name ?? '-' }}</td>
                                        <td>{{ $product->is_active ? 'Active' : 'Inactive' }}</td>
                                        <td class="table-actions">
                                            <div class="action-buttons">
                                                <a href="{{ route('customer.products.edit', $product) }}" class="btn-icon" title="Edit" aria-label="Edit product">
                                                    <svg viewBox="0 0 20 20" aria-hidden="true" focusable="false">
                                                        <path d="M14.7 2.3a1 1 0 0 1 1.4 0l1.6 1.6a1 1 0 0 1 0 1.4l-9.9 9.9a1 1 0 0 1-.45.26l-3.7.9a.75.75 0 0 1-.9-.9l.9-3.7a1 1 0 0 1 .26-.45l9.9-9.9z" fill="currentColor"></path>
                                                        <path d="M12.6 4.4l3 3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path>
                                                    </svg>
                                                </a>
                                                <button
                                                    type="button"
                                                    class="btn-icon"
                                                    title="Delete"
                                                    aria-label="Delete product"
                                                    data-delete-button
                                                    data-delete-action="{{ route('customer.products.destroy', $product) }}"
                                                    data-entity-name="{{ $product->name }}"
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
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </details>
        @empty
            <div class="muted">No products found.</div>
        @endforelse
    </div>
</div>
<div data-view-panel="table" class="{{ $currentView === 'table' ? '' : 'is-hidden' }}">
    <div class="card card-tight">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Brand</th>
                        <th>SKU</th>
                        <th>Unit</th>
                        <th>Status</th>
                        <th class="table-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr>
                            <td>{{ $product->name }}</td>
                            <td>{{ $product->brandClient?->name ?? 'Unassigned' }}</td>
                            <td>{{ $product->sku ?? '-' }}</td>
                            <td>{{ $product->unit?->name ?? '-' }}</td>
                            <td>{{ $product->is_active ? 'Active' : 'Inactive' }}</td>
                            <td class="table-actions">
                                <div class="action-buttons">
                                    <a href="{{ route('customer.products.edit', $product) }}" class="btn-icon" title="Edit" aria-label="Edit product">
                                        <svg viewBox="0 0 20 20" aria-hidden="true" focusable="false">
                                            <path d="M14.7 2.3a1 1 0 0 1 1.4 0l1.6 1.6a1 1 0 0 1 0 1.4l-9.9 9.9a1 1 0 0 1-.45.26l-3.7.9a.75.75 0 0 1-.9-.9l.9-3.7a1 1 0 0 1 .26-.45l9.9-9.9z" fill="currentColor"></path>
                                            <path d="M12.6 4.4l3 3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path>
                                        </svg>
                                    </a>
                                    <button
                                        type="button"
                                        class="btn-icon"
                                        title="Delete"
                                        aria-label="Delete product"
                                        data-delete-button
                                        data-delete-action="{{ route('customer.products.destroy', $product) }}"
                                        data-entity-name="{{ $product->name }}"
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
                            <td colspan="6" class="muted text-center">No products found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{ $products->links() }}

<div class="modal-overlay" id="delete-product-modal" aria-hidden="true">
    <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="delete-product-title">
        <div class="modal-title" id="delete-product-title">Delete product</div>
        <div class="modal-body">
            You are about to delete <span id="delete-product-name">this product</span>. This action cannot be undone.
        </div>
        <div class="modal-actions">
            <button type="button" class="btn btn-secondary" data-modal-cancel>Cancel</button>
            <form method="POST" id="delete-product-form">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-primary">Delete</button>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('delete-product-modal');
        const nameEl = document.getElementById('delete-product-name');
        const form = document.getElementById('delete-product-form');
        const cancelBtn = modal?.querySelector('[data-modal-cancel]');

        const closeModal = () => {
            modal?.classList.remove('is-open');
            modal?.setAttribute('aria-hidden', 'true');
        };

        document.querySelectorAll('[data-delete-button]').forEach((button) => {
            button.addEventListener('click', () => {
                const action = button.getAttribute('data-delete-action');
                const name = button.getAttribute('data-entity-name') || 'this product';

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

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const links = Array.from(document.querySelectorAll('[data-view-link]'));
        const panels = Array.from(document.querySelectorAll('[data-view-panel]'));

        const setView = (view) => {
            panels.forEach((panel) => {
                const isActive = panel.dataset.viewPanel === view;
                panel.classList.toggle('is-hidden', !isActive);
            });

            links.forEach((link) => {
                const isActive = link.dataset.view === view;
                link.classList.toggle('btn-primary', isActive);
                link.classList.toggle('btn-secondary', !isActive);
                link.setAttribute('aria-pressed', isActive ? 'true' : 'false');
            });
        };

        links.forEach((link) => {
            link.addEventListener('click', (event) => {
                event.preventDefault();
                const view = link.dataset.view || 'table';
                setView(view);

                const url = new URL(window.location.href);
                url.searchParams.set('view', view);
                window.history.replaceState({}, '', url);
            });
        });
    });
</script>
@endsection
