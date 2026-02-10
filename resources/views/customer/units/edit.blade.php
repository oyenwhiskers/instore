@extends('layouts.app')

@section('page_title', 'Edit Unit')
@section('page_desc', 'Update the unit details.')
@section('page_actions')
    <a href="{{ route('customer.units.index') }}" class="btn btn-secondary">Back</a>
@endsection

@section('content')
<form method="POST" action="{{ route('customer.units.update', $unit) }}" class="form-section">
    @csrf
    @method('PUT')
    <div class="form-grid">
        <div class="form-group">
            <label>Unit Name</label>
            <input type="text" name="name" value="{{ old('name', $unit->name) }}" class="input" required>
        </div>
    </div>
    <div class="form-actions">
        <a href="{{ route('customer.units.index') }}" class="btn btn-secondary">Cancel</a>
        <button class="btn btn-primary" type="submit">Save Changes</button>
    </div>
</form>
@endsection
