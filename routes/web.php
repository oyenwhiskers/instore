<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Management\KpiTargetController;
use App\Http\Controllers\Management\LocationController;
use App\Http\Controllers\Management\ManagementDashboardController;
use App\Http\Controllers\Management\PlanSettingsController;
use App\Http\Controllers\Management\ManagementReportController;
use App\Http\Controllers\Management\PromoterManagementController;
use App\Http\Controllers\Customer\BrandClientController as CustomerBrandClientController;
use App\Http\Controllers\Customer\CustomerDashboardController;
use App\Http\Controllers\Customer\CustomerKpiProgressController;
use App\Http\Controllers\Customer\CustomerKpiTargetController;
use App\Http\Controllers\Customer\LocationController as CustomerLocationController;
use App\Http\Controllers\Customer\ProductController as CustomerProductController;
use App\Http\Controllers\Customer\PremiumController as CustomerPremiumController;
use App\Http\Controllers\Customer\UnitController as CustomerUnitController;
use App\Http\Controllers\Customer\PromoterController as CustomerPromoterController;
use App\Http\Controllers\Customer\CustomerReportController;
use App\Http\Controllers\Customer\CustomerAssignmentController;
use App\Http\Controllers\Customer\CustomerSubscriptionController;
use App\Http\Controllers\Customer\EventController as CustomerEventController;
use App\Http\Controllers\Promoter\HourlyReportController;
use App\Http\Controllers\Promoter\PromoterCheckinController;
use App\Http\Controllers\Promoter\PromoterDashboardController;
use App\Http\Controllers\Promoter\PromoterKpiController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('landing');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

Route::get('/dashboard', function () {
    $user = Auth::user();

    if (!$user) {
        return redirect()->route('login');
    }

    if ($user->role === 'manager') {
        return redirect()->route('management.dashboard');
    }

    if (in_array($user->role, ['customer_admin', 'customer_staff'], true)) {
        return redirect()->route('customer.dashboard');
    }

    return redirect()->route('promoter.dashboard');
})->middleware('auth')->name('dashboard');

Route::middleware(['auth', 'role:promoter'])->prefix('promoter')->name('promoter.')->group(function () {
    Route::get('/dashboard', [PromoterDashboardController::class, 'index'])->name('dashboard');
    Route::get('/reports/create', [HourlyReportController::class, 'create'])->name('reports.create');
    Route::post('/reports', [HourlyReportController::class, 'store'])->name('reports.store');
    Route::get('/reports/history', [HourlyReportController::class, 'history'])->name('reports.history');
    Route::get('/kpi', [PromoterKpiController::class, 'index'])->name('kpi');
    Route::post('/check-ins', [PromoterCheckinController::class, 'store'])->name('checkins.store');
});

Route::middleware(['auth', 'role:manager'])->prefix('management')->name('management.')->group(function () {
    Route::get('/dashboard', [ManagementDashboardController::class, 'index'])->name('dashboard');
    Route::get('/reports', [ManagementReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/{event}', [ManagementReportController::class, 'show'])->name('reports.show');
    Route::get('/promoters', [PromoterManagementController::class, 'index'])->name('promoters.index');
    Route::get('/promoters/create', [PromoterManagementController::class, 'create'])->name('promoters.create');
    Route::post('/promoters', [PromoterManagementController::class, 'store'])->name('promoters.store');
    Route::get('/promoters/{user}/edit', [PromoterManagementController::class, 'edit'])->name('promoters.edit');
    Route::put('/promoters/{user}', [PromoterManagementController::class, 'update'])->name('promoters.update');
    Route::get('/locations', [LocationController::class, 'index'])->name('locations.index');
    Route::get('/locations/create', [LocationController::class, 'create'])->name('locations.create');
    Route::post('/locations', [LocationController::class, 'store'])->name('locations.store');
    Route::get('/locations/{location}/edit', [LocationController::class, 'edit'])->name('locations.edit');
    Route::put('/locations/{location}', [LocationController::class, 'update'])->name('locations.update');
    Route::get('/kpi-targets', [KpiTargetController::class, 'index'])->name('kpi-targets.index');
    Route::get('/kpi-targets/{user}/edit', [KpiTargetController::class, 'edit'])->name('kpi-targets.edit');
    Route::put('/kpi-targets/{user}', [KpiTargetController::class, 'update'])->name('kpi-targets.update');
    Route::get('/plan-settings', [PlanSettingsController::class, 'index'])->name('plan-settings.index');
    Route::post('/plan-settings/active', [PlanSettingsController::class, 'setActive'])->name('plan-settings.active');
    Route::put('/plan-settings/{plan}', [PlanSettingsController::class, 'update'])->name('plan-settings.update');
});

Route::middleware(['auth', 'role:customer_admin,customer_staff'])->prefix('customer')->name('customer.')->group(function () {
    Route::get('/dashboard', [CustomerDashboardController::class, 'index'])->name('dashboard');

    Route::get('/reports', [CustomerReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export', [CustomerReportController::class, 'export'])->name('reports.export');
    Route::get('/reports/export/pdf', [CustomerReportController::class, 'exportPdf'])->name('reports.export.pdf');
    Route::get('/reports/{report}', [CustomerReportController::class, 'show'])->name('reports.show');

    Route::get('/assignments', [CustomerAssignmentController::class, 'index'])->name('assignments.index');
    Route::get('/brand-clients', [CustomerBrandClientController::class, 'index'])->name('brand-clients.index');
    Route::get('/brand-clients/create', [CustomerBrandClientController::class, 'create'])->name('brand-clients.create');
    Route::post('/brand-clients', [CustomerBrandClientController::class, 'store'])->name('brand-clients.store');
    Route::get('/brand-clients/{brandClient}/edit', [CustomerBrandClientController::class, 'edit'])->name('brand-clients.edit');
    Route::put('/brand-clients/{brandClient}', [CustomerBrandClientController::class, 'update'])->name('brand-clients.update');
    Route::delete('/brand-clients/{brandClient}', [CustomerBrandClientController::class, 'destroy'])->name('brand-clients.destroy');

    Route::get('/products', [CustomerProductController::class, 'index'])->name('products.index');
    Route::get('/products/create', [CustomerProductController::class, 'create'])->name('products.create');
    Route::post('/products', [CustomerProductController::class, 'store'])->name('products.store');
    Route::get('/products/{product}/edit', [CustomerProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{product}', [CustomerProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [CustomerProductController::class, 'destroy'])->name('products.destroy');

    Route::get('/premiums', [CustomerPremiumController::class, 'index'])->name('premiums.index');
    Route::get('/premiums/create', [CustomerPremiumController::class, 'create'])->name('premiums.create');
    Route::post('/premiums', [CustomerPremiumController::class, 'store'])->name('premiums.store');
    Route::get('/premiums/{premium}/edit', [CustomerPremiumController::class, 'edit'])->name('premiums.edit');
    Route::put('/premiums/{premium}', [CustomerPremiumController::class, 'update'])->name('premiums.update');
    Route::delete('/premiums/{premium}', [CustomerPremiumController::class, 'destroy'])->name('premiums.destroy');

    Route::get('/units', [CustomerUnitController::class, 'index'])->name('units.index');
    Route::get('/units/create', [CustomerUnitController::class, 'create'])->name('units.create');
    Route::post('/units', [CustomerUnitController::class, 'store'])->name('units.store');
    Route::get('/units/{unit}/edit', [CustomerUnitController::class, 'edit'])->name('units.edit');
    Route::put('/units/{unit}', [CustomerUnitController::class, 'update'])->name('units.update');
    Route::delete('/units/{unit}', [CustomerUnitController::class, 'destroy'])->name('units.destroy');

    Route::get('/locations', [CustomerLocationController::class, 'index'])->name('locations.index');
    Route::get('/locations/create', [CustomerLocationController::class, 'create'])->name('locations.create');
    Route::post('/locations', [CustomerLocationController::class, 'store'])->name('locations.store');
    Route::get('/locations/{location}/edit', [CustomerLocationController::class, 'edit'])->name('locations.edit');
    Route::put('/locations/{location}', [CustomerLocationController::class, 'update'])->name('locations.update');

    Route::get('/events', [CustomerEventController::class, 'index'])->name('events.index');
    Route::get('/events/create', [CustomerEventController::class, 'create'])->name('events.create');
    Route::post('/events', [CustomerEventController::class, 'store'])->name('events.store');
    Route::get('/events/{event}', [CustomerEventController::class, 'show'])->name('events.show');
    Route::get('/events/{event}/edit', [CustomerEventController::class, 'edit'])->name('events.edit');
    Route::put('/events/{event}', [CustomerEventController::class, 'update'])->name('events.update');
    Route::put('/events/{event}/schedule', [CustomerEventController::class, 'updateSchedule'])->name('events.schedule.update');
    Route::post('/events/{event}/stock-movements', [CustomerEventController::class, 'storeStockMovement'])->name('events.stock-movements.store');
    Route::post('/events/{event}/stock-balances', [CustomerEventController::class, 'updateStockBalances'])->name('events.stock-balances.update');
    Route::post('/events/{event}/kpis', [CustomerEventController::class, 'updateKpis'])->name('events.kpis.update');

    Route::get('/promoters', [CustomerPromoterController::class, 'index'])->name('promoters.index');
    Route::get('/promoters/create', [CustomerPromoterController::class, 'create'])->name('promoters.create');
    Route::post('/promoters', [CustomerPromoterController::class, 'store'])->name('promoters.store');
    Route::get('/promoters/{promoter}/edit', [CustomerPromoterController::class, 'edit'])->name('promoters.edit');
    Route::put('/promoters/{promoter}', [CustomerPromoterController::class, 'update'])->name('promoters.update');

    Route::get('/kpi-targets', [CustomerKpiTargetController::class, 'index'])->name('kpi-targets.index');
    Route::get('/kpi-targets/{promoter}/edit', [CustomerKpiTargetController::class, 'edit'])->name('kpi-targets.edit');
    Route::put('/kpi-targets/{promoter}', [CustomerKpiTargetController::class, 'update'])->name('kpi-targets.update');

    Route::get('/kpi-progress/{promoter}', [CustomerKpiProgressController::class, 'show'])->name('kpi-progress.show');

    Route::get('/subscription', [CustomerSubscriptionController::class, 'show'])->name('subscription.show');
});
