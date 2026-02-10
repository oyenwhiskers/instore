<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerSubscriptionController extends Controller
{
    public function show(Request $request): View
    {
        $company = Company::with(['plan', 'brandClients', 'products', 'locations', 'users'])
            ->findOrFail($request->user()->company_id);

        $usage = [
            'brand_clients' => $company->brandClients->count(),
            'products' => $company->products->count(),
            'locations' => $company->locations->count(),
            'promoters' => $company->users->where('role', 'promoter')->count(),
        ];

        return view('customer.subscription.show', [
            'company' => $company,
            'usage' => $usage,
        ]);
    }
}
