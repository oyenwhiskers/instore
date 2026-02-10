@extends('layouts.app')

@section('page_title', 'Add Brand Client')
@section('page_desc', 'Register a new brand under your company profile.')
@section('page_actions')
    <a href="{{ route('customer.brand-clients.index') }}" class="btn btn-secondary">Back</a>
@endsection

@section('content')
<div class="card">
    <form method="POST" action="{{ route('customer.brand-clients.store') }}" class="form-section">
        @csrf
        <div class="form-grid">
            <div class="form-group">
                <label>Brand Name</label>
                <input type="text" name="name" value="{{ old('name') }}" class="input" required>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="status" class="select">
                    <option value="active" @selected(old('status') === 'active')>Active</option>
                    <option value="inactive" @selected(old('status') === 'inactive')>Inactive</option>
                </select>
            </div>
        </div>
        <button class="btn btn-primary" type="submit">Create Brand</button>
    </form>
</div>
@endsection
