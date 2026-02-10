@extends('layouts.app')

@section('page_title', 'Add Promoter')
@section('page_desc', 'Create a promoter profile for your activation team.')
@section('page_actions')
    <a href="{{ route('customer.promoters.index') }}" class="btn btn-secondary">Back</a>
@endsection

@section('content')
<form method="POST" action="{{ route('customer.promoters.store') }}" class="form-section">
        @csrf

        <div class="section-block">
            <div class="stat-label">Promoter Login</div>
            <div class="form-grid compact">
                <div class="form-group">
                    <label class="label-with-tip">
                        Promoter ID
                        <span class="tooltip" tabindex="0" data-tip="Auto-generated after you save. Used for promoter login.">?</span>
                    </label>
                    <input type="text" value="Auto-generated" class="input" readonly>
                </div>
                <div class="form-group">
                    <label class="label-with-tip">
                        IC Number
                        <span class="tooltip" tabindex="0" data-tip="Promoter enters this IC with their Promoter ID to log in.">?</span>
                    </label>
                    <input type="text" name="ic_number" value="{{ old('ic_number') }}" class="input" required>
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
                    <input type="text" name="name" value="{{ old('name') }}" class="input" required>
                </div>
                <div class="form-group">
                    <label class="label-with-tip">
                        Phone
                        <span class="tooltip" tabindex="0" data-tip="Optional contact number for the promoter.">?</span>
                    </label>
                    <input type="text" name="phone" value="{{ old('phone') }}" class="input">
                </div>
                <div class="form-group">
                    <label class="label-with-tip">
                        Email
                        <span class="tooltip" tabindex="0" data-tip="Optional. Work email for internal contact only.">?</span>
                    </label>
                    <input type="email" name="email" value="{{ old('email') }}" class="input">
                </div>
                <div class="form-group narrow">
                    <label class="label-with-tip">
                        Status
                        <span class="tooltip" tabindex="0" data-tip="Inactive promoters cannot log in or submit reports.">?</span>
                    </label>
                    <select name="status" class="select">
                        <option value="active" @selected(old('status') === 'active')>Active</option>
                        <option value="inactive" @selected(old('status') === 'inactive')>Inactive</option>
                    </select>
                </div>
            </div>
        </div>

        <button class="btn btn-primary" type="submit">Create Promoter</button>
    </form>
@endsection
