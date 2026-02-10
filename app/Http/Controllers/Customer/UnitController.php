<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UnitController extends Controller
{
    public function index(Request $request): View
    {
        $companyId = $request->user()->company_id;

        $query = Unit::where('company_id', $companyId)->with('createdBy');
        $search = trim((string) $request->input('search'));
        if ($search !== '') {
            $query->where('name', 'like', '%' . $search . '%');
        }

        if ($request->filled('created_from')) {
            $query->whereDate('created_at', '>=', $request->input('created_from'));
        }

        if ($request->filled('created_to')) {
            $query->whereDate('created_at', '<=', $request->input('created_to'));
        }

        $units = $query->orderByRaw('LOWER(name)')
            ->paginate(20)
            ->withQueryString();

        return view('customer.units.index', [
            'units' => $units,
            'filters' => [
                'search' => $search,
                'created_from' => $request->input('created_from'),
                'created_to' => $request->input('created_to'),
            ],
        ]);
    }

    public function create(): View
    {
        return view('customer.units.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $companyId = $request->user()->company_id;

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        Unit::create([
            'company_id' => $companyId,
            'created_by' => $request->user()->id,
            'name' => $data['name'],
        ]);

        return redirect()->route('customer.units.index')->with('status', 'Unit created.');
    }

    public function edit(Unit $unit): View
    {
        if ($unit->company_id !== auth()->user()->company_id) {
            abort(403, 'Unauthorized');
        }

        return view('customer.units.edit', [
            'unit' => $unit,
        ]);
    }

    public function update(Request $request, Unit $unit): RedirectResponse
    {
        if ($unit->company_id !== $request->user()->company_id) {
            abort(403, 'Unauthorized');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $unit->update($data);

        return redirect()->route('customer.units.index')->with('status', 'Unit updated.');
    }

    public function destroy(Request $request, Unit $unit): RedirectResponse
    {
        if ($unit->company_id !== $request->user()->company_id) {
            abort(403, 'Unauthorized');
        }

        if ($unit->products()->exists()) {
            return redirect()->route('customer.units.index')->withErrors([
                'unit' => 'Unit cannot be deleted while products are assigned to it.',
            ]);
        }

        $unit->delete();

        return redirect()->route('customer.units.index')->with('status', 'Unit deleted.');
    }
}
