@extends('layouts.app')

@section('page_title', 'Edit Promoter')
@section('page_desc', 'Update promoter profile details.')
@section('page_actions')
    <a href="{{ route('customer.promoters.index') }}" class="btn btn-secondary">Back</a>
@endsection

@section('content')
<form method="POST" action="{{ route('customer.promoters.update', $promoter) }}" class="form-section">
        @csrf
        @method('PUT')

        <div class="section-block">
            <div class="stat-label">Promoter Login</div>
            <div class="form-grid compact">
                <div class="form-group">
                    <label class="label-with-tip">
                        Promoter ID
                        <span class="tooltip" tabindex="0" data-tip="Used for promoter login.">?</span>
                    </label>
                    <input type="text" value="{{ $promoter->promoter_id ?? 'Not set' }}" class="input" readonly>
                </div>
                <div class="form-group">
                    <label class="label-with-tip">
                        IC Number
                        <span class="tooltip" tabindex="0" data-tip="Promoter enters this IC with their Promoter ID to log in.">?</span>
                    </label>
                    <input type="text" name="ic_number" value="{{ old('ic_number', $promoter->ic_number) }}" class="input" required>
                </div>
            </div>
        </div>

        <div class="section-block">
            <div class="stat-label">Personal Details</div>
            <div class="form-grid compact">
                <div class="form-group">
                    <label class="label-with-tip">
                        Name
                        <span class="tooltip" tabindex="0" data-tip="Promoter full name as shown on reports.">?</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name', $promoter->name) }}" class="input" required>
                </div>
                <div class="form-group">
                    <label class="label-with-tip">
                        Phone
                        <span class="tooltip" tabindex="0" data-tip="Optional contact number for the promoter.">?</span>
                    </label>
                    <input type="text" name="phone" value="{{ old('phone', $promoter->phone) }}" class="input">
                </div>
                <div class="form-group">
                    <label class="label-with-tip">
                        Email
                        <span class="tooltip" tabindex="0" data-tip="Optional. Work email for internal contact only.">?</span>
                    </label>
                    <input type="email" name="email" value="{{ old('email', $promoter->email) }}" class="input">
                </div>
                <div class="form-group narrow">
                    <label class="label-with-tip">
                        Status
                        <span class="tooltip" tabindex="0" data-tip="Inactive promoters cannot log in or submit reports.">?</span>
                    </label>
                    <select name="status" class="select">
                        <option value="active" @selected(old('status', $promoter->status) === 'active')>Active</option>
                        <option value="inactive" @selected(old('status', $promoter->status) === 'inactive')>Inactive</option>
                    </select>
                </div>
            </div>
        </div>

        <button class="btn btn-primary" type="submit">Save Changes</button>
    </form>
@endsection
