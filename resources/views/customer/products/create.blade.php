@extends('layouts.app')

@section('page_title', 'Add Product')
@section('page_desc', 'Create a product under a specific brand client.')
@section('page_actions')
    <a href="{{ route('customer.products.index') }}" class="btn btn-secondary">Back</a>
@endsection

@section('content')
<div class="card">
    <form method="POST" action="{{ route('customer.products.store') }}" class="form-section">
        @csrf
        <div class="form-grid compact three-col">
            <div class="form-group form-span-2">
                <label>Brand Client</label>
                <select name="brand_client_id" class="select">
                    <option value="">Unassigned</option>
                    @foreach ($brandClients as $client)
                        <option value="{{ $client->id }}" @selected(old('brand_client_id') == $client->id)>{{ $client->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="is_active" class="select">
                    <option value="1" @selected(old('is_active', '1') === '1')>Active</option>
                    <option value="0" @selected(old('is_active') === '0')>Inactive</option>
                </select>
            </div>
            <div class="form-group">
                <label>Product Name</label>
                <input type="text" name="name" value="{{ old('name') }}" class="input" required>
            </div>
            <div class="form-group">
                <label>SKU</label>
                <input type="text" name="sku" value="{{ old('sku') }}" class="input">
            </div>
            <div class="form-group">
                <label>Unit</label>
                <select name="unit_id" class="select">
                    <option value="">Unassigned</option>
                    @foreach ($units as $unit)
                        <option value="{{ $unit->id }}" @selected(old('unit_id') == $unit->id)>{{ $unit->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <button class="btn btn-primary" type="submit">Create Product</button>
    </form>
</div>
@endsection
