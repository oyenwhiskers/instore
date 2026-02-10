@extends('layouts.app')

@section('page_title', 'Create Promoter')
@section('page_desc', 'Add a promoter profile.')
@section('page_actions')
    <a href="{{ route('management.promoters.index') }}" class="btn btn-secondary">Back to List</a>
@endsection

@section('content')
<div class="card">
    <form method="POST" action="{{ route('management.promoters.store') }}" class="form-section">
        @csrf

        <div class="form-section">
            <div class="stat-label">Profile Details</div>
            <div class="form-grid">
                <div class="form-group">
                    <label>Promoter ID</label>
                    <input type="text" value="Auto-generated" class="input" readonly>
                </div>
                <div class="form-group">
                    <label>Name</label>
                    <input type="text" name="name" value="{{ old('name') }}" class="input" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="input">
                </div>
                <div class="form-group">
                    <label>IC Number</label>
                    <input type="text" name="ic_number" value="{{ old('ic_number') }}" class="input" required>
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" class="input">
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="select">
                        <option value="active" @selected(old('status') === 'active')>Active</option>
                        <option value="inactive" @selected(old('status') === 'inactive')>Inactive</option>
                    </select>
                </div>
            </div>
        </div>

        <button class="btn btn-primary" type="submit">Create Promoter</button>
    </form>
</div>
@endsection
