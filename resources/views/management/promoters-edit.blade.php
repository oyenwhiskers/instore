@extends('layouts.app')

@section('page_title', 'Edit Promoter')
@section('page_desc', 'Maintain profile accuracy and access status.')
@section('page_actions')
    <a href="{{ route('management.promoters.index') }}" class="btn btn-secondary">Back to List</a>
@endsection

@section('content')
<div class="card">
    <form method="POST" action="{{ route('management.promoters.update', $promoter) }}" class="form-section">
        @csrf
        @method('PUT')

        <div class="form-section">
            <div class="stat-label">Profile Details</div>
            <div class="form-grid">
                <div class="form-group">
                    <label>Promoter ID</label>
                    <input type="text" value="{{ $promoter->promoter_id ?? 'Not set' }}" class="input" readonly>
                </div>
                <div class="form-group">
                    <label>Name</label>
                    <input type="text" name="name" value="{{ old('name', $promoter->name) }}" class="input" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="{{ old('email', $promoter->email) }}" class="input">
                </div>
                <div class="form-group">
                    <label>IC Number</label>
                    <input type="text" name="ic_number" value="{{ old('ic_number', $promoter->ic_number) }}" class="input">
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="phone" value="{{ old('phone', $promoter->phone) }}" class="input">
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="select">
                        <option value="active" @selected(old('status', $promoter->status) === 'active')>Active</option>
                        <option value="inactive" @selected(old('status', $promoter->status) === 'inactive')>Inactive</option>
                    </select>
                </div>
            </div>
        </div>

        <button class="btn btn-primary" type="submit">Save Changes</button>
    </form>
</div>
@endsection
