@extends('layouts.app')

@section('page_title', 'Add Premium')
@section('page_desc', 'Create a premium gift and a simple note for staff.')
@section('page_actions')
    <a href="{{ route('customer.premiums.index') }}" class="btn btn-secondary">Back</a>
@endsection

@section('content')
<form method="POST" action="{{ route('customer.premiums.store') }}" class="form-section">
    @csrf
    <div class="section-block">
        <div class="stat-label">Premium Details</div>
        <div class="form-grid one-col">
            <div class="form-group">
                <label class="label-with-tip">
                    Gift Name
                    <span class="tooltip" tabindex="0" data-tip="Name of the gift (e.g., Mug, Tote Bag).">?</span>
                </label>
                <input type="text" name="gift_name" value="{{ old('gift_name') }}" class="input" required>
            </div>
            <div class="form-group">
                <label class="label-with-tip">
                    Note
                    <span class="tooltip" tabindex="0" data-tip="Simple internal note about the mechanic (e.g., Spend RM100).">?</span>
                </label>
                <textarea name="mechanic_description" class="textarea" rows="3" required>{{ old('mechanic_description') }}</textarea>
            </div>
        </div>
    </div>

    <div class="form-actions split">
        <a href="{{ route('customer.premiums.index') }}" class="btn btn-secondary">Cancel</a>
        <button class="btn btn-primary" type="submit">Create Premium</button>
    </div>
</form>

@endsection
