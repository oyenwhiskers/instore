<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\PromoterProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PromoterController extends Controller
{
    public function index(Request $request): View
    {
        $companyId = $request->user()->company_id;
        $query = User::where('company_id', $companyId)
            ->where('role', 'promoter')
            ->with('promoterProfile');

        $search = trim((string) $request->input('search'));
        if ($search !== '') {
            $query->where(function ($searchQuery) use ($search) {
                $searchQuery
                    ->where('name', 'like', '%' . $search . '%')
                    ->orWhere('promoter_id', 'like', '%' . $search . '%');
            });
        }

        $status = $request->input('status');
        if (in_array($status, ['active', 'inactive'], true)) {
            $query->where('status', $status);
        }

        $promoters = $query->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('customer.promoters.index', [
            'promoters' => $promoters,
            'filters' => [
                'search' => $search,
                'status' => $status,
            ],
        ]);
    }

    public function create(Request $request): View
    {
        return view('customer.promoters.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $companyId = $request->user()->company_id;
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')],
            'ic_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('users', 'ic_number')->where('company_id', $companyId),
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
            'company_id' => $companyId,
        ]);

        PromoterProfile::create([
            'user_id' => $user->id,
        ]);

        return redirect()->route('customer.promoters.index')->with('status', 'Promoter created. ID: ' . $promoterId);
    }

    public function edit(User $promoter): View
    {
        if ($promoter->company_id !== auth()->user()->company_id) {
            abort(403, 'Unauthorized');
        }

        $promoter->load('promoterProfile', 'assignment');
        return view('customer.promoters.edit', [
            'promoter' => $promoter,
        ]);
    }

    public function update(Request $request, User $promoter): RedirectResponse
    {
        if ($promoter->company_id !== $request->user()->company_id) {
            abort(403, 'Unauthorized');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($promoter->id)],
            'ic_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('users', 'ic_number')
                    ->where('company_id', $promoter->company_id)
                    ->ignore($promoter->id),
            ],
            'phone' => ['nullable', 'string', 'max:30'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $promoter->update([
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'ic_number' => $data['ic_number'],
            'phone' => $data['phone'] ?? null,
            'status' => $data['status'],
        ]);

        $profile = $promoter->promoterProfile ?: new PromoterProfile(['user_id' => $promoter->id]);
        $profile->save();

        return redirect()->route('customer.promoters.index')->with('status', 'Promoter updated.');
    }

    private function generatePromoterId(): string
    {
        do {
            $promoterId = 'PRM-' . Str::upper(Str::random(6));
        } while (User::where('promoter_id', $promoterId)->exists());

        return $promoterId;
    }
}
