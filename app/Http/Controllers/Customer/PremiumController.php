<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Premium;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PremiumController extends Controller
{
    public function index(Request $request): View
    {
        $companyId = $request->user()->company_id;

        $query = Premium::where('company_id', $companyId)->with('createdBy');
        $search = trim((string) $request->input('search'));
        if ($search !== '') {
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('gift_name', 'like', '%' . $search . '%')
                    ->orWhere('mechanic_description', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('created_from')) {
            $query->whereDate('created_at', '>=', $request->input('created_from'));
        }

        if ($request->filled('created_to')) {
            $query->whereDate('created_at', '<=', $request->input('created_to'));
        }

        $premiums = $query->orderByRaw('LOWER(gift_name)')
            ->paginate(20)
            ->withQueryString();

        return view('customer.premiums.index', [
            'premiums' => $premiums,
            'filters' => [
                'search' => $search,
                'created_from' => $request->input('created_from'),
                'created_to' => $request->input('created_to'),
            ],
        ]);
    }

    public function create(): View
    {
        return view('customer.premiums.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $companyId = $request->user()->company_id;

        $data = $request->validate([
            'gift_name' => ['required', 'string', 'max:255'],
            'mechanic_description' => ['required', 'string', 'max:2000'],
        ]);

        Premium::create([
            'company_id' => $companyId,
            'created_by' => $request->user()->id,
            'gift_name' => $data['gift_name'],
            'mechanic_description' => $data['mechanic_description'],
        ]);

        return redirect()->route('customer.premiums.index')->with('status', 'Premium created.');
    }

    public function edit(Premium $premium): View
    {
        if ($premium->company_id !== auth()->user()->company_id) {
            abort(403, 'Unauthorized');
        }

        return view('customer.premiums.edit', [
            'premium' => $premium,
        ]);
    }

    public function update(Request $request, Premium $premium): RedirectResponse
    {
        if ($premium->company_id !== $request->user()->company_id) {
            abort(403, 'Unauthorized');
        }

        $data = $request->validate([
            'gift_name' => ['required', 'string', 'max:255'],
            'mechanic_description' => ['required', 'string', 'max:2000'],
        ]);

        $premium->update($data);

        return redirect()->route('customer.premiums.index')->with('status', 'Premium updated.');
    }

    public function destroy(Request $request, Premium $premium): RedirectResponse
    {
        if ($premium->company_id !== $request->user()->company_id) {
            abort(403, 'Unauthorized');
        }

        if ($premium->events()->exists()) {
            return redirect()->route('customer.premiums.index')->withErrors([
                'premium' => 'Premium cannot be deleted while it is assigned to events.',
            ]);
        }

        $premium->delete();

        return redirect()->route('customer.premiums.index')->with('status', 'Premium deleted.');
    }
}
