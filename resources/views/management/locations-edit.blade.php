@extends('layouts.app')

@section('page_title', 'Edit Location')
@section('page_desc', 'Update outlet details and maintain accurate geo metadata.')
@section('page_actions')
    <a href="{{ route('management.locations.index') }}" class="btn btn-secondary">Back to Locations</a>
@endsection

@section('content')
<div class="card">
    <form method="POST" action="{{ route('management.locations.update', $location) }}" class="form-section">
        @csrf
        @method('PUT')

        <div class="form-section">
            <div class="stat-label">Location Details</div>
            <div class="form-grid">
                <div class="form-group">
                    <label>Name</label>
                    <input type="text" name="name" value="{{ old('name', $location->name) }}" class="input" required>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="select">
                        <option value="active" @selected(old('status', $location->status) === 'active')>Active</option>
                        <option value="inactive" @selected(old('status', $location->status) === 'inactive')>Inactive</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Address</label>
                <textarea name="address" class="textarea" rows="3">{{ old('address', $location->address) }}</textarea>
            </div>
        </div>

        <div class="form-section">
            <div class="stat-label">Geo Coordinates</div>
            <div class="form-grid">
                <div class="form-group">
                    <label>Latitude</label>
                    <input type="text" name="geo_lat" value="{{ old('geo_lat', $location->geo_lat) }}" class="input">
                </div>
                <div class="form-group">
                    <label>Longitude</label>
                    <input type="text" name="geo_lng" value="{{ old('geo_lng', $location->geo_lng) }}" class="input">
                </div>
            </div>
        </div>

        <div class="form-section">
            <div class="stat-label">Products To Promote</div>
            <div class="form-grid">
                @php $selectedProducts = old('product_ids', $location->products->pluck('id')->all()); @endphp
                @foreach ($products as $product)
                    <label class="text-sm">
                        <input type="checkbox" name="product_ids[]" value="{{ $product->id }}" @checked(in_array($product->id, $selectedProducts))>
                        {{ $product->name }}
                    </label>
                @endforeach
            </div>
        </div>

        <button class="btn btn-primary" type="submit">Save Changes</button>
    </form>
</div>
@endsection
