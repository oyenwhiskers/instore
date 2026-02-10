<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function show(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['nullable', 'string'],
            'ic_number' => ['nullable', 'string'],
        ]);

        $loginValue = $data['login'];
        $remember = $request->boolean('remember');

        if (str_contains($loginValue, '@')) {
            if (empty($data['password'])) {
                return back()->withErrors([
                    'password' => 'Password is required for email login.',
                ])->onlyInput('login');
            }

            if (Auth::attempt(['email' => $loginValue, 'password' => $data['password']], $remember)) {
                $request->session()->regenerate();

                return redirect()->intended('/dashboard');
            }
        } else {
            if (empty($data['ic_number'])) {
                return back()->withErrors([
                    'ic_number' => 'IC number is required for promoter login.',
                ])->onlyInput('login');
            }

            $user = User::where('promoter_id', $loginValue)
                ->where('ic_number', $data['ic_number'])
                ->where('role', 'promoter')
                ->first();

            if ($user) {
                Auth::login($user, $remember);
                $request->session()->regenerate();

                return redirect()->intended('/dashboard');
            }
        }

        return back()->withErrors([
            'login' => 'The provided credentials do not match our records.',
        ])->onlyInput('login');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
