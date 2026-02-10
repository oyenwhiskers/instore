<?php
namespace App\Http\Controllers\Customer;
use App\Http\Controllers\Controller;
use App\Models\BrandClient;
use App\Models\Event;
use App\Models\EventPromoterKpi;
use App\Models\EventPromoterPremiumTarget;
use App\Models\EventStockMovement;
use App\Models\Location;
use App\Models\Product;
use App\Models\Premium;
use App\Models\PromoterCheckin;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
class EventController extends Controller
{
    public function index(Request $request): View
    {
        $companyId = $request->user()->company_id;
        $query = Event::where('company_id', $companyId)
            ->with(['location', 'promoters', 'products']);

        $locationSearch = trim((string) $request->input('location'));
        if ($locationSearch !== '') {
            $query->whereHas('location', function ($locationQuery) use ($locationSearch) {
                $locationQuery->where('name', 'like', '%' . $locationSearch . '%');
            });
        }

        $status = $request->input('status');
        if (in_array($status, ['planned', 'active', 'completed', 'cancelled'], true)) {
            $query->where('status', $status);
        }

        $events = $query->orderByDesc('start_date')
            ->paginate(20)
            ->withQueryString();

        return view('customer.events.index', [
            'events' => $events,
            'filters' => [
                'location' => $locationSearch,
                'status' => $status,
            ],
        ]);
    }

    public function create(Request $request): View
    {
        $companyId = $request->user()->company_id;

        $locations = Location::where('company_id', $companyId)->orderBy('name')->get();
        $brandClients = BrandClient::where('company_id', $companyId)->orderBy('name')->get();
        $promoters = User::where('company_id', $companyId)->where('role', 'promoter')->orderBy('name')->get();
        $products = Product::where('company_id', $companyId)->orderBy('name')->get();
        $premiums = Premium::where('company_id', $companyId)->orderBy('gift_name')->get();

        return view('customer.events.create', [
            'locations' => $locations,
            'brandClients' => $brandClients,
            'promoters' => $promoters,
            'products' => $products,
            'premiums' => $premiums,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $companyId = $request->user()->company_id;

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'location_id' => [
                'required',
                Rule::exists('locations', 'id')->where('company_id', $companyId),
            ],
            'status' => ['required', 'in:planned,active,completed,cancelled'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'promoter_ids' => ['array'],
            'promoter_ids.*' => [
                'integer',
                Rule::exists('users', 'id')->where('company_id', $companyId)->where('role', 'promoter'),
            ],
            'brand_client_ids' => ['array'],
            'brand_client_ids.*' => ['integer', 'exists:brand_clients,id'],
            'product_ids' => ['array'],
            'product_ids.*' => [
                'integer',
                Rule::exists('products', 'id')->where('company_id', $companyId),
            ],
            'premium_ids' => ['array'],
            'premium_ids.*' => [
                'integer',
                Rule::exists('premiums', 'id')->where('company_id', $companyId),
            ],
            'promoter_schedule' => ['array'],
            'promoter_schedule.*.start_date' => ['nullable', 'date'],
            'promoter_schedule.*.end_date' => ['nullable', 'date'],
            'promoter_schedule.*.start_time' => ['nullable'],
            'promoter_schedule.*.end_time' => ['nullable'],
            
        ]);

        $selectedBrandClientIds = collect($data['brand_client_ids'] ?? [])
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($selectedBrandClientIds->isNotEmpty()) {
            $availableBrandClientIds = BrandClient::where('company_id', $companyId)
                ->whereIn('id', $selectedBrandClientIds)
                ->pluck('id')
                ->all();

            if (count($availableBrandClientIds) !== $selectedBrandClientIds->count()) {
                return back()->withErrors([
                    'brand_client_ids' => 'Selected brand clients are not available.',
                ])->withInput();
            }
        }

        $selectedProductIds = $data['product_ids'] ?? [];
        if (!empty($selectedProductIds)) {
            if ($selectedBrandClientIds->isEmpty()) {
                return back()->withErrors([
                    'product_ids' => 'Select brand clients before choosing products.',
                ])->withInput();
            }

            $availableProductIds = Product::where('company_id', $companyId)
                ->whereIn('brand_client_id', $selectedBrandClientIds)
                ->pluck('id')
                ->all();

            $invalidProducts = array_diff($selectedProductIds, $availableProductIds);
            if (!empty($invalidProducts)) {
                return back()->withErrors([
                    'product_ids' => 'Selected products must belong to the chosen brand clients.',
                ])->withInput();
            }
        }

        $event = Event::create([
            'company_id' => $companyId,
            'location_id' => $data['location_id'],
            'created_by' => $request->user()->id,
            'name' => $data['name'],
            'status' => $data['status'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'notes' => $data['notes'] ?? null,
        ]);

        $promoterSync = [];
        foreach ($data['promoter_ids'] ?? [] as $promoterId) {
            $schedule = $data['promoter_schedule'][$promoterId] ?? [];
            $promoterSync[$promoterId] = [
                'start_date' => $schedule['start_date'] ?? null,
                'end_date' => $schedule['end_date'] ?? null,
                'start_time' => $schedule['start_time'] ?? null,
                'end_time' => $schedule['end_time'] ?? null,
                
            ];
        }

        $event->promoters()->sync($promoterSync);
        $event->products()->sync($selectedProductIds);
        $event->premiums()->sync($data['premium_ids'] ?? []);

        return redirect()->route('customer.events.index')->with('status', 'Event created.');
    }

    public function show(Event $event): View
    {
        if ($event->company_id !== auth()->user()->company_id) {
            abort(403, 'Unauthorized');
        }

        $event->load(['location', 'promoters', 'products', 'premiums', 'createdBy', 'updatedBy']);

        $stockSearch = trim((string) request()->input('stock_search'));
        $stockType = request()->input('stock_type');
        $stockDate = request()->input('stock_date');

        $stockMovementsAll = $event->stockMovements()->get();

        $stockMovementsQuery = $event->stockMovements()
            ->with(['product', 'createdBy', 'updatedBy'])
            ->latest();

        if ($stockSearch !== '') {
            $stockMovementsQuery->whereHas('product', function ($query) use ($stockSearch) {
                $query->where('name', 'like', '%' . $stockSearch . '%');
            });
        }

        if (in_array($stockType, ['in', 'out'], true)) {
            $stockMovementsQuery->where('movement_type', $stockType);
        }

        if ($stockDate) {
            $start = Carbon::parse($stockDate)->startOfDay();
            $end = Carbon::parse($stockDate)->endOfDay();
            $stockMovementsQuery->whereBetween('created_at', [$start, $end]);
        }

        $stockMovements = $stockMovementsQuery->get();

        $stockBalances = $event->products
            ->mapWithKeys(function ($product) use ($stockMovementsAll) {
                $balance = $stockMovementsAll
                    ->where('product_id', $product->id)
                    ->reduce(function ($carry, $movement) {
                        $direction = $movement->movement_type === 'out' ? -1 : 1;
                        return $carry + ($movement->quantity * $direction);
                    }, 0);

                return [$product->id => $balance];
            });

        $attendanceByPromoter = collect();
        $latestCheckinsByPromoter = collect();
        $attendanceSearch = trim((string) request()->input('attendance_search'));
        $attendanceStatus = request()->input('attendance_status');
        $attendanceDate = request()->input('attendance_date');
        $attendancePromoters = $event->promoters;
        if ($event->location_id && $event->start_date && $event->end_date && $event->promoters->isNotEmpty()) {
            if ($attendanceDate) {
                $start = Carbon::parse($attendanceDate)->startOfDay();
                $end = Carbon::parse($attendanceDate)->endOfDay();
            } else {
                $start = Carbon::parse($event->start_date)->startOfDay();
                $end = Carbon::parse($event->end_date)->endOfDay();
            }
            $promoterIds = $event->promoters->pluck('id');

            $checkins = PromoterCheckin::where('location_id', $event->location_id)
                ->whereIn('user_id', $promoterIds)
                ->whereBetween('check_in_at', [$start, $end])
                ->orderByDesc('check_in_at')
                ->get();

            $attendanceByPromoter = $checkins->groupBy('user_id')->map(function ($items) {
                return [
                    'count' => $items->count(),
                    'last_check_in' => $items->first()?->check_in_at,
                ];
            });

            $latestCheckinsByPromoter = $checkins->groupBy('user_id')->map(function ($items) {
                return $items->first();
            });
        }

        if ($attendanceSearch !== '') {
            $attendancePromoters = $attendancePromoters->filter(function ($promoter) use ($attendanceSearch) {
                return stripos($promoter->name ?? '', $attendanceSearch) !== false;
            })->values();
        }

        if (in_array($attendanceStatus, ['checked', 'missing'], true)) {
            $attendancePromoters = $attendancePromoters->filter(function ($promoter) use ($attendanceByPromoter, $attendanceStatus) {
                $hasCheckin = ($attendanceByPromoter->get($promoter->id)['count'] ?? 0) > 0;
                return $attendanceStatus === 'checked' ? $hasCheckin : !$hasCheckin;
            })->values();
        }

        $promoterKpis = $event->promoterKpis()
            ->with(['promoter', 'createdBy', 'updatedBy'])
            ->get()
            ->keyBy('promoter_user_id');

        $premiumTargets = EventPromoterPremiumTarget::where('event_id', $event->id)
            ->get()
            ->groupBy('promoter_user_id')
            ->map(function ($items) {
                return $items->keyBy('premium_id');
            });

        return view('customer.events.show', [
            'event' => $event,
            'stockMovements' => $stockMovements,
            'stockBalances' => $stockBalances,
            'stockFilters' => [
                'search' => $stockSearch,
                'type' => $stockType,
                'date' => $stockDate,
            ],
            'attendanceByPromoter' => $attendanceByPromoter,
            'latestCheckinsByPromoter' => $latestCheckinsByPromoter,
            'attendancePromoters' => $attendancePromoters,
            'attendanceFilters' => [
                'search' => $attendanceSearch,
                'status' => $attendanceStatus,
                'date' => $attendanceDate,
            ],
            'promoterKpis' => $promoterKpis,
            'premiumTargets' => $premiumTargets,
        ]);
    }

    public function edit(Event $event): View
    {
        if ($event->company_id !== auth()->user()->company_id) {
            abort(403, 'Unauthorized');
        }

        $companyId = $event->company_id;
        $event->load(['promoters', 'products', 'premiums', 'location']);

        $locations = Location::where('company_id', $companyId)->orderBy('name')->get();
        $brandClients = BrandClient::where('company_id', $companyId)->orderBy('name')->get();
        $promoters = User::where('company_id', $companyId)->where('role', 'promoter')->orderBy('name')->get();
        $products = Product::where('company_id', $companyId)->orderBy('name')->get();
        $premiums = Premium::where('company_id', $companyId)->orderBy('gift_name')->get();

        return view('customer.events.edit', [
            'event' => $event,
            'locations' => $locations,
            'brandClients' => $brandClients,
            'promoters' => $promoters,
            'products' => $products,
            'premiums' => $premiums,
        ]);
    }

    public function update(Request $request, Event $event): RedirectResponse
    {
        if ($event->company_id !== $request->user()->company_id) {
            abort(403, 'Unauthorized');
        }

        $companyId = $event->company_id;

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'location_id' => [
                'required',
                Rule::exists('locations', 'id')->where('company_id', $companyId),
            ],
            'status' => ['required', 'in:planned,active,completed,cancelled'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'promoter_ids' => ['array'],
            'promoter_ids.*' => [
                'integer',
                Rule::exists('users', 'id')->where('company_id', $companyId)->where('role', 'promoter'),
            ],
            'brand_client_ids' => ['array'],
            'brand_client_ids.*' => ['integer', 'exists:brand_clients,id'],
            'product_ids' => ['array'],
            'product_ids.*' => [
                'integer',
                Rule::exists('products', 'id')->where('company_id', $companyId),
            ],
            'premium_ids' => ['array'],
            'premium_ids.*' => [
                'integer',
                Rule::exists('premiums', 'id')->where('company_id', $companyId),
            ],
            'promoter_schedule' => ['array'],
            'promoter_schedule.*.start_date' => ['nullable', 'date'],
            'promoter_schedule.*.end_date' => ['nullable', 'date'],
            'promoter_schedule.*.start_time' => ['nullable'],
            'promoter_schedule.*.end_time' => ['nullable'],
            
        ]);

        $selectedBrandClientIds = collect($data['brand_client_ids'] ?? [])
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($selectedBrandClientIds->isNotEmpty()) {
            $availableBrandClientIds = BrandClient::where('company_id', $companyId)
                ->whereIn('id', $selectedBrandClientIds)
                ->pluck('id')
                ->all();

            if (count($availableBrandClientIds) !== $selectedBrandClientIds->count()) {
                return back()->withErrors([
                    'brand_client_ids' => 'Selected brand clients are not available.',
                ])->withInput();
            }
        }

        $selectedProductIds = $data['product_ids'] ?? [];
        if (!empty($selectedProductIds)) {
            if ($selectedBrandClientIds->isEmpty()) {
                return back()->withErrors([
                    'product_ids' => 'Select brand clients before choosing products.',
                ])->withInput();
            }

            $availableProductIds = Product::where('company_id', $companyId)
                ->whereIn('brand_client_id', $selectedBrandClientIds)
                ->pluck('id')
                ->all();

            $invalidProducts = array_diff($selectedProductIds, $availableProductIds);
            if (!empty($invalidProducts)) {
                return back()->withErrors([
                    'product_ids' => 'Selected products must belong to the chosen brand clients.',
                ])->withInput();
            }
        }

        $event->update([
            'location_id' => $data['location_id'],
            'updated_by' => $request->user()->id,
            'name' => $data['name'],
            'status' => $data['status'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'notes' => $data['notes'] ?? null,
        ]);

        $promoterSync = [];
        foreach ($data['promoter_ids'] ?? [] as $promoterId) {
            $schedule = $data['promoter_schedule'][$promoterId] ?? [];
            $promoterSync[$promoterId] = [
                'start_date' => $schedule['start_date'] ?? null,
                'end_date' => $schedule['end_date'] ?? null,
                'start_time' => $schedule['start_time'] ?? null,
                'end_time' => $schedule['end_time'] ?? null,
                
            ];
        }

        $event->promoters()->sync($promoterSync);
        $event->products()->sync($selectedProductIds);
        $event->premiums()->sync($data['premium_ids'] ?? []);

        return redirect()->route('customer.events.index')->with('status', 'Event updated.');
    }

    public function storeStockMovement(Request $request, Event $event): RedirectResponse
    {
        if ($event->company_id !== $request->user()->company_id) {
            abort(403, 'Unauthorized');
        }

        $data = $request->validate([
            'product_id' => ['required', 'integer', Rule::exists('products', 'id')->where('company_id', $event->company_id)],
            'movement_type' => ['required', 'in:in,out'],
            'quantity' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $isEventProduct = $event->products()->where('products.id', $data['product_id'])->exists();
        if (!$isEventProduct) {
            return back()->withErrors([
                'product_id' => 'Selected product is not linked to this event.',
            ])->withInput();
        }

        EventStockMovement::create([
            'event_id' => $event->id,
            'product_id' => $data['product_id'],
            'movement_type' => $data['movement_type'],
            'quantity' => $data['quantity'],
            'notes' => $data['notes'] ?? null,
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('customer.events.show', $event)->with('status', 'Stock movement added.');
    }

    public function updateStockBalances(Request $request, Event $event): RedirectResponse
    {
        if ($event->company_id !== $request->user()->company_id) {
            abort(403, 'Unauthorized');
        }

        $data = $request->validate([
            'adjustments' => ['array'],
            'adjustments.*' => ['nullable', 'integer'],
            'prices' => ['array'],
            'prices.*' => ['nullable', 'numeric', 'min:0'],
        ]);

        $adjustments = $data['adjustments'] ?? [];
        $prices = $data['prices'] ?? [];

        if (empty($adjustments) && empty($prices)) {
            return redirect()->route('customer.events.show', $event);
        }

        $event->load('products');
        $allowedProductIds = $event->products()->pluck('products.id')->all();
        $stockMovements = $event->stockMovements()->get();
        $currentBalances = $event->products
            ->mapWithKeys(function ($product) use ($stockMovements) {
                $balance = $stockMovements
                    ->where('product_id', $product->id)
                    ->reduce(function ($carry, $movement) {
                        $direction = $movement->movement_type === 'out' ? -1 : 1;
                        return $carry + ($movement->quantity * $direction);
                    }, 0);

                return [$product->id => $balance];
            });

        $userId = $request->user()->id;

        foreach ($adjustments as $productId => $adjustment) {
            if (!in_array((int) $productId, $allowedProductIds, true)) {
                continue;
            }

            if ($adjustment === '' || $adjustment === null) {
                continue;
            }

            $delta = (int) $adjustment;
            if ($delta !== 0) {
                EventStockMovement::create([
                    'event_id' => $event->id,
                    'product_id' => (int) $productId,
                    'movement_type' => $delta > 0 ? 'in' : 'out',
                    'quantity' => abs($delta),
                    'notes' => 'Quantity adjusted in Manage Products.',
                    'created_by' => $userId,
                ]);
            }
        }

        foreach ($prices as $productId => $price) {
            if (!in_array((int) $productId, $allowedProductIds, true)) {
                continue;
            }

            if ($price === '' || $price === null) {
                continue;
            }

            $event->products()->updateExistingPivot((int) $productId, [
                'unit_price' => $price,
            ]);
        }

        return redirect()->route('customer.events.show', $event)->with('status', 'Products updated.');
    }

    public function updateKpis(Request $request, Event $event): RedirectResponse
    {
        if ($event->company_id !== $request->user()->company_id) {
            abort(403, 'Unauthorized');
        }

        $data = $request->validate([
            'kpis' => ['array'],
            'kpis.*.target_sales_amount' => ['nullable', 'numeric', 'min:0'],
            'kpis.*.target_engagements' => ['nullable', 'integer', 'min:0'],
            'kpis.*.target_samplings' => ['nullable', 'integer', 'min:0'],
            'premium_targets' => ['array'],
            'premium_targets.*' => ['array'],
            'premium_targets.*.*' => ['nullable', 'integer', 'min:0'],
        ]);

        $allowedPromoterIds = $event->promoters()->pluck('users.id')->all();
        $allowedPremiumIds = $event->premiums()->pluck('premiums.id')->all();
        $userId = $request->user()->id;
        $kpis = $data['kpis'] ?? [];
        $premiumTargets = $data['premium_targets'] ?? [];

        foreach ($kpis as $promoterId => $values) {
            if (!in_array((int) $promoterId, $allowedPromoterIds, true)) {
                continue;
            }

            $payload = [
                'target_sales_amount' => ($values['target_sales_amount'] ?? '') === '' ? null : $values['target_sales_amount'],
                'target_engagements' => ($values['target_engagements'] ?? '') === '' ? null : $values['target_engagements'],
                'target_samplings' => ($values['target_samplings'] ?? '') === '' ? null : $values['target_samplings'],
            ];

            $kpi = EventPromoterKpi::firstOrNew([
                'event_id' => $event->id,
                'promoter_user_id' => $promoterId,
            ]);

            if (!$kpi->exists) {
                $kpi->created_by = $userId;
            }

            $kpi->fill($payload);
            $kpi->updated_by = $userId;
            $kpi->save();
        }

        foreach ($premiumTargets as $promoterId => $targets) {
            if (!in_array((int) $promoterId, $allowedPromoterIds, true)) {
                continue;
            }

            foreach ($targets as $premiumId => $targetQty) {
                if (!in_array((int) $premiumId, $allowedPremiumIds, true)) {
                    continue;
                }

                $targetValue = ($targetQty === '' || $targetQty === null) ? 0 : (int) $targetQty;

                $target = EventPromoterPremiumTarget::firstOrNew([
                    'event_id' => $event->id,
                    'promoter_user_id' => (int) $promoterId,
                    'premium_id' => (int) $premiumId,
                ]);

                if (!$target->exists) {
                    $target->created_by = $userId;
                }

                $target->target_qty = $targetValue;
                $target->updated_by = $userId;
                $target->save();
            }
        }

        return redirect()->route('customer.events.show', $event)->with('status', 'Promoter KPI targets updated.');
    }

    public function updateSchedule(Request $request, Event $event): RedirectResponse
    {
        if ($event->company_id !== $request->user()->company_id) {
            abort(403, 'Unauthorized');
        }

        $data = $request->validate([
            'promoter_id' => ['required', 'integer'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],
        ]);

        $promoterId = (int) $data['promoter_id'];
        $isAttached = $event->promoters()->where('users.id', $promoterId)->exists();
        if (!$isAttached) {
            return back()->withErrors([
                'promoter_id' => 'Selected promoter is not assigned to this event.',
            ]);
        }

        $event->promoters()->updateExistingPivot($promoterId, [
            'start_date' => $data['start_date'] ?? null,
            'end_date' => $data['end_date'] ?? null,
            'start_time' => isset($data['start_time']) ? $data['start_time'] . ':00' : null,
            'end_time' => isset($data['end_time']) ? $data['end_time'] . ':00' : null,
        ]);

        return redirect()->route('customer.events.show', $event)->with('status', 'Promoter schedule updated.');
    }
}
