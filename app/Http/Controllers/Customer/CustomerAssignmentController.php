<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\PromoterAssignment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerAssignmentController extends Controller
{
    public function index(Request $request): View
    {
        $companyId = $request->user()->company_id;

        $query = PromoterAssignment::whereHas('location', function ($builder) use ($companyId) {
            $builder->where('company_id', $companyId);
        })->with(['promoter', 'location']);

        if ($request->filled('location_id')) {
            $query->where('location_id', $request->input('location_id'));
        }

        if ($request->filled('promoter_user_id')) {
            $query->where('user_id', $request->input('promoter_user_id'));
        }

        if ($request->filled('start_date')) {
            $query->whereDate('start_date', '>=', $request->input('start_date'));
        }

        if ($request->filled('end_date')) {
            $query->whereDate('end_date', '<=', $request->input('end_date'));
        }

        $assignments = $query->orderByDesc('start_date')
            ->paginate(20)
            ->withQueryString();

        $locations = Location::where('company_id', $companyId)->orderBy('name')->get();
        $promoters = User::where('company_id', $companyId)->where('role', 'promoter')->orderBy('name')->get();

        return view('customer.assignments.index', [
            'assignments' => $assignments,
            'locations' => $locations,
            'promoters' => $promoters,
        ]);
    }
}
