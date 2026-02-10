<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\BrandClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BrandClientController extends Controller
{
    public function index(Request $request): View
    {
        $companyId = $request->user()->company_id;
        $query = BrandClient::where('company_id', $companyId)->with('createdBy');
        $search = trim((string) $request->input('search'));
        if ($search !== '') {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $status = $request->input('status');
        if (in_array($status, ['active', 'inactive'], true)) {
            $query->where('status', $status);
        }

        if ($request->filled('created_from')) {
            $query->whereDate('created_at', '>=', $request->input('created_from'));
        }

        if ($request->filled('created_to')) {
            $query->whereDate('created_at', '<=', $request->input('created_to'));
        }

        $clients = $query->orderByRaw('LOWER(name)')->paginate(20)->withQueryString();

        return view('customer.brand-clients.index', [
            'clients' => $clients,
            'filters' => [
                'search' => $search,
                'status' => $status,
                'created_from' => $request->input('created_from'),
                'created_to' => $request->input('created_to'),
            ],
        ]);
    }

    public function create(): View
    {
        return view('customer.brand-clients.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $companyId = $request->user()->company_id;
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        BrandClient::create([
            'company_id' => $companyId,
            'created_by' => $request->user()->id,
            'name' => $data['name'],
            'status' => $data['status'],
        ]);

        return redirect()->route('customer.brand-clients.index')->with('status', 'Brand client created.');
    }

    public function edit(BrandClient $brandClient): View
    {
        if ($brandClient->company_id !== auth()->user()->company_id) {
            abort(403, 'Unauthorized');
        }

        return view('customer.brand-clients.edit', [
            'brandClient' => $brandClient,
        ]);
    }

    public function update(Request $request, BrandClient $brandClient): RedirectResponse
    {
        if ($brandClient->company_id !== $request->user()->company_id) {
            abort(403, 'Unauthorized');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $brandClient->update($data);

        return redirect()->route('customer.brand-clients.index')->with('status', 'Brand client updated.');
    }

    public function destroy(Request $request, BrandClient $brandClient): RedirectResponse
    {
        if ($brandClient->company_id !== $request->user()->company_id) {
            abort(403, 'Unauthorized');
        }

        if ($brandClient->products()->exists() || $brandClient->locations()->exists()) {
            return redirect()->route('customer.brand-clients.index')->withErrors([
                'brand_client' => 'Brand client cannot be deleted while products or locations are assigned.',
            ]);
        }

        $brandClient->delete();

        return redirect()->route('customer.brand-clients.index')->with('status', 'Brand client deleted.');
    }
}
