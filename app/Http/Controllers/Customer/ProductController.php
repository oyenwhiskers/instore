<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\BrandClient;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $companyId = $request->user()->company_id;
        $brandClients = BrandClient::where('company_id', $companyId)->orderBy('name')->get();
        $brandClientIds = $brandClients->pluck('id')->all();

        $viewMode = $request->input('view', 'table');
        if (!in_array($viewMode, ['table', 'group'], true)) {
            $viewMode = 'table';
        }

        $query = Product::where('company_id', $companyId)->with(['brandClient', 'unit']);
        $search = trim((string) $request->input('search'));
        if ($search !== '') {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $brandFilter = $request->input('brand_client_id');
        if (!empty($brandFilter) && in_array((int) $brandFilter, $brandClientIds, true)) {
            $query->where('brand_client_id', $brandFilter);
        }

        if ($request->filled('created_from')) {
            $query->whereDate('created_at', '>=', $request->input('created_from'));
        }

        if ($request->filled('created_to')) {
            $query->whereDate('created_at', '<=', $request->input('created_to'));
        }

        $products = $query->orderByRaw('LOWER(name)')->paginate(20)->withQueryString();

        return view('customer.products.index', [
            'products' => $products,
            'brandClients' => $brandClients,
            'viewMode' => $viewMode,
            'filters' => [
                'search' => $search,
                'brand_client_id' => $brandFilter,
                'created_from' => $request->input('created_from'),
                'created_to' => $request->input('created_to'),
            ],
        ]);
    }

    public function create(Request $request): View
    {
        $companyId = $request->user()->company_id;
        $brandClients = BrandClient::where('company_id', $companyId)->orderBy('name')->get();
        $units = Unit::where('company_id', $companyId)->orderBy('name')->get();

        return view('customer.products.create', [
            'brandClients' => $brandClients,
            'units' => $units,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $companyId = $request->user()->company_id;
        $plan = $request->user()->company?->plan;

        if ($plan && $plan->max_products) {
            $currentCount = Product::where('company_id', $companyId)->count();
            if ($currentCount >= $plan->max_products) {
                return back()->withErrors([
                    'limit' => 'Product limit reached for the current plan.',
                ])->withInput();
            }
        }

        $data = $request->validate([
            'brand_client_id' => ['nullable', 'exists:brand_clients,id'],
            'unit_id' => ['nullable', 'exists:units,id'],
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:100'],
            'is_active' => ['required', 'boolean'],
        ]);

        if (!empty($data['brand_client_id'])) {
            $brandClient = BrandClient::where('company_id', $companyId)
                ->where('id', $data['brand_client_id'])
                ->first();
            if (!$brandClient) {
                return back()->withErrors([
                    'brand_client_id' => 'Selected brand client is not available.',
                ])->withInput();
            }
        }

        if (!empty($data['unit_id'])) {
            $unit = Unit::where('company_id', $companyId)
                ->where('id', $data['unit_id'])
                ->first();
            if (!$unit) {
                return back()->withErrors([
                    'unit_id' => 'Selected unit is not available.',
                ])->withInput();
            }
        }

        Product::create([
            'company_id' => $companyId,
            'brand_client_id' => $data['brand_client_id'] ?? null,
            'unit_id' => $data['unit_id'] ?? null,
            'name' => $data['name'],
            'sku' => $data['sku'] ?? null,
            'is_active' => $data['is_active'],
        ]);

        return redirect()->route('customer.products.index')->with('status', 'Product created.');
    }

    public function edit(Product $product): View
    {
        if ($product->company_id !== auth()->user()->company_id) {
            abort(403, 'Unauthorized');
        }

        $brandClients = BrandClient::where('company_id', $product->company_id)->orderBy('name')->get();
        $units = Unit::where('company_id', $product->company_id)->orderBy('name')->get();

        return view('customer.products.edit', [
            'product' => $product,
            'brandClients' => $brandClients,
            'units' => $units,
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        if ($product->company_id !== $request->user()->company_id) {
            abort(403, 'Unauthorized');
        }

        $data = $request->validate([
            'brand_client_id' => ['nullable', 'exists:brand_clients,id'],
            'unit_id' => ['nullable', 'exists:units,id'],
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:100'],
            'is_active' => ['required', 'boolean'],
        ]);

        if (!empty($data['brand_client_id'])) {
            $brandClient = BrandClient::where('company_id', $product->company_id)
                ->where('id', $data['brand_client_id'])
                ->first();
            if (!$brandClient) {
                return back()->withErrors([
                    'brand_client_id' => 'Selected brand client is not available.',
                ])->withInput();
            }
        }

        if (!empty($data['unit_id'])) {
            $unit = Unit::where('company_id', $product->company_id)
                ->where('id', $data['unit_id'])
                ->first();
            if (!$unit) {
                return back()->withErrors([
                    'unit_id' => 'Selected unit is not available.',
                ])->withInput();
            }
        }

        $product->update($data);

        return redirect()->route('customer.products.index')->with('status', 'Product updated.');
    }

    public function destroy(Request $request, Product $product): RedirectResponse
    {
        if ($product->company_id !== $request->user()->company_id) {
            abort(403, 'Unauthorized');
        }

        if ($product->events()->exists() || $product->locations()->exists() || $product->reportItems()->exists()) {
            return redirect()->route('customer.products.index')->withErrors([
                'product' => 'Product cannot be deleted while it is used in events, locations, or reports.',
            ]);
        }

        $product->delete();

        return redirect()->route('customer.products.index')->with('status', 'Product deleted.');
    }
}
