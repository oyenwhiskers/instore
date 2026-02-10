@extends('layouts.app')

@section('page_title', 'Create Access Profile')
@section('page_desc', 'Provide verified details for secure promoter access.')

@section('content')
<div class="card" style="max-width: 620px;">
    <form method="POST" action="{{ route('register.store') }}" class="form-section">
        @csrf
        <div class="form-grid">
            <div class="form-group">
                <label for="name">Full Name</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" class="input" required>
            </div>
            <div class="form-group">
                <label for="email">Work Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" class="input" required>
            </div>
            <div class="form-group">
                <label for="ic_number">IC Number</label>
                <input id="ic_number" type="text" name="ic_number" value="{{ old('ic_number') }}" class="input">
            </div>
            <div class="form-group">
                <label for="phone">Phone</label>
                <input id="phone" type="text" name="phone" value="{{ old('phone') }}" class="input">
            </div>
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label for="password">Password</label>
                <input id="password" type="password" name="password" class="input" required>
            </div>
            <div class="form-group">
                <label for="password_confirmation">Confirm Password</label>
                <input id="password_confirmation" type="password" name="password_confirmation" class="input" required>
            </div>
        </div>

        <button class="btn btn-primary" type="submit">Create Account</button>
    </form>
</div>
@endsection
