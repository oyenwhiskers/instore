@extends('layouts.app')

@section('page_title', 'Add Unit')
@section('page_desc', 'Create a new unit for your products.')
@section('page_actions')
    <a href="{{ route('customer.units.index') }}" class="btn btn-secondary">Back</a>
@endsection

@section('content')
<form method="POST" action="{{ route('customer.units.store') }}" class="form-section">
    @csrf
    <div class="form-grid">
        <div class="form-group">
            <label>Unit Name</label>
            <input type="text" name="name" value="{{ old('name') }}" class="input" required>
        </div>
    </div>
    <div class="form-actions">
        <a href="{{ route('customer.units.index') }}" class="btn btn-secondary">Cancel</a>
        <button class="btn btn-primary" type="submit">Create Unit</button>
    </div>
</form>
@endsection
