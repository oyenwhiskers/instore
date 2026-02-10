@extends('layouts.app')

@section('page_title', 'Secure Login')
@section('page_desc', 'Access your workspace with role-based controls and auditability.')

@section('content')
<div class="card" style="max-width: 520px;">
    <form method="POST" action="{{ route('login.store') }}" class="form-section">
        @csrf
        <div class="form-group">
            <label for="login">Promoter ID or Email</label>
            <input id="login" type="text" name="login" value="{{ old('login') }}" class="input" required>
        </div>
        <div class="form-group">
            <label for="ic_number">IC Number (Promoter)</label>
            <input id="ic_number" type="text" name="ic_number" value="{{ old('ic_number') }}" class="input">
        </div>
        <div class="form-group">
            <label for="password">Password (Admin/Customer)</label>
            <input id="password" type="password" name="password" class="input">
        </div>
        <div class="form-group">
            <label for="remember" class="text-xs">
                <input type="checkbox" name="remember" id="remember">
                Remember this device
            </label>
        </div>
        <button class="btn btn-primary" type="submit">Login</button>
    </form>
</div>
@endsection
