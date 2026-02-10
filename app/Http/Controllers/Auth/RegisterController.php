<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function show(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'ic_number' => ['nullable', 'string', 'max:50', 'unique:users,ic_number'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'ic_number' => $data['ic_number'] ?? null,
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
            'role' => 'promoter',
            'status' => 'active',
        ]);

        Auth::login($user);

        return redirect('/dashboard');
    }
}
