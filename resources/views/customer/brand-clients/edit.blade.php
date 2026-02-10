@extends('layouts.app')

@section('page_title', 'Edit Brand Client')
@section('page_desc', 'Update brand status and profile details.')
@section('page_actions')
    <a href="{{ route('customer.brand-clients.index') }}" class="btn btn-secondary">Back</a>
@endsection

@section('content')
<div class="card">
    <form method="POST" action="{{ route('customer.brand-clients.update', $brandClient) }}" class="form-section">
        @csrf
        @method('PUT')
        <div class="form-grid">
            <div class="form-group">
                <label>Brand Name</label>
                <input type="text" name="name" value="{{ old('name', $brandClient->name) }}" class="input" required>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="status" class="select">
                    <option value="active" @selected(old('status', $brandClient->status) === 'active')>Active</option>
                    <option value="inactive" @selected(old('status', $brandClient->status) === 'inactive')>Inactive</option>
                </select>
            </div>
        </div>
        <button class="btn btn-primary" type="submit">Save Changes</button>
    </form>
</div>
@endsection
