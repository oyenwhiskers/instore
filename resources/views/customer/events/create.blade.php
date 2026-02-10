@extends('layouts.app')

@section('page_title', 'Add Event')
@section('page_desc', 'Create an activation event with promoters, location, and products.')
@section('page_actions')
    <a href="{{ route('customer.events.index') }}" class="btn btn-secondary">Back</a>
@endsection

@section('content')
@php
    $selectedPromoters = old('promoter_ids', []);
    $selectedProducts = old('product_ids', []);
    $selectedBrandClients = old('brand_client_ids', []);
    $selectedPremiums = old('premium_ids', []);
@endphp
<form method="POST" action="{{ route('customer.events.store') }}" class="form-section">
    @csrf

    <div class="section-block">
        <div class="stat-label">Event Details</div>
        <div class="form-grid event-details-grid">
            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" value="{{ old('name') }}" class="input" required>
            </div>
            <div class="form-group">
                <label>Location</label>
                <select name="location_id" class="select" required>
                    <option value="">Select location</option>
                    @foreach ($locations as $location)
                        <option value="{{ $location->id }}" @selected(old('location_id') == $location->id)>{{ $location->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="status" class="select">
                    <option value="planned" @selected(old('status') === 'planned')>Planned</option>
                    <option value="active" @selected(old('status') === 'active')>Active</option>
                    <option value="completed" @selected(old('status') === 'completed')>Completed</option>
                    <option value="cancelled" @selected(old('status') === 'cancelled')>Cancelled</option>
                </select>
            </div>
            <div class="form-group">
                <label>Start Date</label>
                <input type="date" name="start_date" value="{{ old('start_date') }}" class="input" required>
            </div>
            <div class="form-group">
                <label>End Date</label>
                <input type="date" name="end_date" value="{{ old('end_date') }}" class="input" required>
            </div>
        </div>
        <div class="form-group">
            <label>Notes</label>
            <textarea name="notes" class="textarea" rows="2">{{ old('notes') }}</textarea>
        </div>
    </div>

    <div class="section-block">
        <div class="stat-label">Promoters</div>
        <div class="event-promoter-grid">
            @foreach ($promoters as $promoter)
                <div class="event-promoter-card">
                    <label class="event-promoter-head">
                        <input type="checkbox" name="promoter_ids[]" value="{{ $promoter->id }}" @checked(in_array($promoter->id, $selectedPromoters))>
                        <span>{{ $promoter->name }}</span>
                    </label>
                    <div class="event-promoter-fields">
                        <div class="form-group">
                            <label>Start Date</label>
                            <input type="date" name="promoter_schedule[{{ $promoter->id }}][start_date]" value="{{ old('promoter_schedule.' . $promoter->id . '.start_date') }}" class="input">
                        </div>
                        <div class="form-group">
                            <label>End Date</label>
                            <input type="date" name="promoter_schedule[{{ $promoter->id }}][end_date]" value="{{ old('promoter_schedule.' . $promoter->id . '.end_date') }}" class="input">
                        </div>
                        <div class="form-group">
                            <label>Start Time</label>
                            <input type="time" name="promoter_schedule[{{ $promoter->id }}][start_time]" value="{{ old('promoter_schedule.' . $promoter->id . '.start_time') }}" class="input">
                        </div>
                        <div class="form-group">
                            <label>End Time</label>
                            <input type="time" name="promoter_schedule[{{ $promoter->id }}][end_time]" value="{{ old('promoter_schedule.' . $promoter->id . '.end_time') }}" class="input">
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="section-block">
        <div class="stat-label">Products</div>
        <div class="form-grid compact">
            <div class="form-group">
                <label>Search Brand</label>
                <input type="text" id="event-brand-search" class="input" placeholder="Search brand">
            </div>
            <div class="form-group">
                <label>Search Product</label>
                <input type="text" id="event-product-search" class="input" placeholder="Search product name or SKU">
            </div>
        </div>
        <div class="form-grid">
            @foreach ($brandClients as $client)
                <label class="text-sm" data-brand-label="{{ strtolower($client->name) }}">
                    <input type="checkbox" name="brand_client_ids[]" value="{{ $client->id }}" data-brand-client-select @checked(in_array($client->id, $selectedBrandClients))>
                    {{ $client->name }}
                </label>
            @endforeach
        </div>
        <div class="form-note muted">Select brand clients to view their products.</div>
        @php $productsByClient = $products->groupBy('brand_client_id'); @endphp
        @foreach ($brandClients as $client)
            @php $clientProducts = $productsByClient->get($client->id, collect()); @endphp
            <div class="client-product-group" data-product-group data-brand-client="{{ $client->id }}" data-brand-name="{{ strtolower($client->name) }}">
                <div class="client-product-header">
                    <div class="client-product-name">{{ $client->name }}</div>
                </div>
                <div class="client-product-body">
                    @forelse ($clientProducts as $product)
                        <label class="text-sm" data-product-option data-product-name="{{ strtolower($product->name . ' ' . ($product->sku ?? '')) }}">
                            <input type="checkbox" name="product_ids[]" value="{{ $product->id }}" @checked(in_array($product->id, $selectedProducts))>
                            {{ $product->name }}
                        </label>
                    @empty
                        <div class="muted text-sm">No products available for this brand client.</div>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>

    <div class="section-block">
        <div class="stat-label">Premiums</div>
        @if ($premiums->isEmpty())
            <div class="muted">No premiums available yet.</div>
        @else
            <div class="form-grid">
                @foreach ($premiums as $premium)
                    <label class="text-sm">
                        <input type="checkbox" name="premium_ids[]" value="{{ $premium->id }}" @checked(in_array($premium->id, $selectedPremiums))>
                        <strong>{{ $premium->gift_name }}</strong>
                        <span class="muted">- {{ $premium->mechanic_description }}</span>
                    </label>
                @endforeach
            </div>
        @endif
    </div>

    <button class="btn btn-primary" type="submit">Create Event</button>
</form>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const brandInputs = Array.from(document.querySelectorAll('[data-brand-client-select]'));
        const brandLabels = Array.from(document.querySelectorAll('[data-brand-label]'));
        const productGroups = Array.from(document.querySelectorAll('[data-product-group]'));
        const productOptions = Array.from(document.querySelectorAll('[data-product-option]'));
        const brandSearch = document.getElementById('event-brand-search');
        const productSearch = document.getElementById('event-product-search');

        const updateProducts = () => {
            const selectedIds = brandInputs.filter((input) => input.checked).map((input) => input.value);
            const hasSelection = selectedIds.length > 0;
            const brandQuery = (brandSearch?.value || '').trim().toLowerCase();
            const productQuery = (productSearch?.value || '').trim().toLowerCase();

            brandLabels.forEach((label) => {
                const brandName = label.dataset.brandLabel || '';
                const isVisible = brandQuery === '' || brandName.includes(brandQuery);
                label.style.display = isVisible ? '' : 'none';
            });

            productGroups.forEach((group) => {
                const groupBrandId = group.dataset.brandClient;
                const brandName = group.dataset.brandName || '';
                const brandMatches = brandQuery === '' || brandName.includes(brandQuery);
                const isSelected = hasSelection && selectedIds.includes(groupBrandId);
                let groupVisible = isSelected && brandMatches;

                const groupProducts = Array.from(group.querySelectorAll('[data-product-option]'));
                let hasProductMatch = false;
                groupProducts.forEach((option) => {
                    const productName = option.dataset.productName || '';
                    const productVisible = productQuery === '' || productName.includes(productQuery);
                    option.style.display = productVisible ? '' : 'none';
                    if (productVisible) {
                        hasProductMatch = true;
                    }
                });

                if (productQuery !== '') {
                    groupVisible = groupVisible && hasProductMatch;
                }

                group.style.display = groupVisible ? '' : 'none';

                if (!groupVisible) {
                    group.querySelectorAll('input[type="checkbox"][name="product_ids[]"]').forEach((checkbox) => {
                        checkbox.checked = false;
                    });
                }
            });
        };

        brandInputs.forEach((input) => input.addEventListener('change', updateProducts));
        brandSearch?.addEventListener('input', updateProducts);
        productSearch?.addEventListener('input', updateProducts);
        updateProducts();

    });
</script>
@endsection
