<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\PromoterProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PromoterManagementController extends Controller
{
    public function index(): View
    {
        $promoters = User::where('role', 'promoter')
            ->with('promoterProfile')
            ->orderBy('name')
            ->paginate(20);

        return view('management.promoters-index', [
            'promoters' => $promoters,
        ]);
    }

    public function create(): View
    {
        return view('management.promoters-create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')],
            'ic_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('users', 'ic_number')->where('company_id', $request->input('company_id')),
            ],
            'phone' => ['nullable', 'string', 'max:30'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $promoterId = $this->generatePromoterId();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'promoter_id' => $promoterId,
            'ic_number' => $data['ic_number'],
            'phone' => $data['phone'] ?? null,
            'status' => $data['status'],
            'password' => Hash::make(Str::random(32)),
            'role' => 'promoter',
        ]);

        PromoterProfile::create([
            'user_id' => $user->id,
        ]);

        return redirect()->route('management.promoters.index')->with('status', 'Promoter created. ID: ' . $promoterId);
    }

    public function edit(User $user): View
    {
        $user->load('promoterProfile', 'assignment');
        return view('management.promoters-edit', [
            'promoter' => $user,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'ic_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('users', 'ic_number')
                    ->where('company_id', $user->company_id)
                    ->ignore($user->id),
            ],
            'phone' => ['nullable', 'string', 'max:30'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'ic_number' => $data['ic_number'] ?? null,
            'phone' => $data['phone'] ?? null,
            'status' => $data['status'],
        ]);

        $profile = $user->promoterProfile ?: new PromoterProfile(['user_id' => $user->id]);
        $profile->save();

        return redirect()->route('management.promoters.index')->with('status', 'Promoter updated.');
    }

    private function generatePromoterId(): string
    {
        do {
            $promoterId = 'PRM-' . Str::upper(Str::random(6));
        } while (User::where('promoter_id', $promoterId)->exists());

        return $promoterId;
    }
}
