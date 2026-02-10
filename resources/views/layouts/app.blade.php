<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Instore' }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=ibm-plex-sans:400,500,600,700&display=swap" rel="stylesheet" />
    <link href="https://fonts.bunny.net/css?family=space-grotesk:400,500,600,700&display=swap" rel="stylesheet" />
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body>
    <div class="app-shell">
        <aside class="sidebar">
            <div>
                <a href="/" class="brand">Instore</a>
                <div class="brand-sub">Built by SwiftX</div>
            </div>
            <nav class="nav-list">
                @auth
                    @if (auth()->user()->role === 'promoter')
                        <a href="{{ route('promoter.dashboard') }}" class="nav-item {{ request()->routeIs('promoter.dashboard') ? 'active' : '' }}">Dashboard</a>
                        <a href="{{ route('promoter.reports.create') }}" class="nav-item {{ request()->routeIs('promoter.reports.create') ? 'active' : '' }}">Submit Report</a>
                        <a href="{{ route('promoter.reports.history') }}" class="nav-item {{ request()->routeIs('promoter.reports.history') ? 'active' : '' }}">History</a>
                        <a href="{{ route('promoter.kpi') }}" class="nav-item {{ request()->routeIs('promoter.kpi') ? 'active' : '' }}">KPI Progress</a>
                    @elseif (in_array(auth()->user()->role, ['customer_admin', 'customer_staff'], true))
                        <div class="sidebar-section">Insights</div>
                        <a href="{{ route('customer.dashboard') }}" class="nav-item {{ request()->routeIs('customer.dashboard') ? 'active' : '' }}">Dashboard</a>
                        <a href="{{ route('customer.reports.index') }}" class="nav-item {{ request()->routeIs('customer.reports.*') ? 'active' : '' }}">Reports</a>
                        <a href="{{ route('customer.kpi-targets.index') }}" class="nav-item {{ request()->routeIs('customer.kpi-targets.*') ? 'active' : '' }}">KPI Targets</a>

                        <div class="sidebar-section">Operations</div>
                        <a href="{{ route('customer.events.index') }}" class="nav-item {{ request()->routeIs('customer.events.*') ? 'active' : '' }}">Events</a>
                        <a href="{{ route('customer.promoters.index') }}" class="nav-item {{ request()->routeIs('customer.promoters.*') ? 'active' : '' }}">Promoters</a>
                        <a href="{{ route('customer.locations.index') }}" class="nav-item {{ request()->routeIs('customer.locations.*') ? 'active' : '' }}">Locations</a>

                        <div class="sidebar-section">Catalog</div>
                        <a href="{{ route('customer.brand-clients.index') }}" class="nav-item {{ request()->routeIs('customer.brand-clients.*') ? 'active' : '' }}">Brand Clients</a>
                        <a href="{{ route('customer.products.index') }}" class="nav-item {{ request()->routeIs('customer.products.*') ? 'active' : '' }}">Products</a>
                        <a href="{{ route('customer.premiums.index') }}" class="nav-item {{ request()->routeIs('customer.premiums.*') ? 'active' : '' }}">Premiums</a>
                        <a href="{{ route('customer.units.index') }}" class="nav-item {{ request()->routeIs('customer.units.*') ? 'active' : '' }}">Units</a>

                        <div class="sidebar-section">Account</div>
                        <a href="{{ route('customer.subscription.show') }}" class="nav-item {{ request()->routeIs('customer.subscription.*') ? 'active' : '' }}">Subscription</a>
                    @else
                        <a href="{{ route('management.dashboard') }}" class="nav-item {{ request()->routeIs('management.dashboard') ? 'active' : '' }}">Dashboard</a>
                        <a href="{{ route('management.reports.index') }}" class="nav-item {{ request()->routeIs('management.reports.*') ? 'active' : '' }}">Reports</a>
                        <a href="{{ route('management.promoters.index') }}" class="nav-item {{ request()->routeIs('management.promoters.*') ? 'active' : '' }}">Promoters</a>
                        <a href="{{ route('management.locations.index') }}" class="nav-item {{ request()->routeIs('management.locations.*') ? 'active' : '' }}">Locations</a>
                        <a href="{{ route('management.kpi-targets.index') }}" class="nav-item {{ request()->routeIs('management.kpi-targets.*') ? 'active' : '' }}">KPI Targets</a>
                        <a href="{{ route('management.plan-settings.index') }}" class="nav-item {{ request()->routeIs('management.plan-settings.*') ? 'active' : '' }}">Plan Settings</a>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="nav-item">Login</a>
                @endauth
            </nav>
        </aside>

        <div class="content">
            <header class="topbar">
                <div>
                    <div class="page-title">@yield('page_title', 'Instore')</div>
                    <div class="page-desc">@yield('page_desc', 'Performance tracking workspace')</div>
                </div>
                <div class="page-actions">
                    <div class="action-group">
                        @yield('page_actions')
                    </div>
                    @auth
                        <div class="action-divider"></div>
                        <div class="action-group">
                            <span class="badge">{{ ucfirst(auth()->user()->role) }}</span>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button class="btn btn-secondary" type="submit">Logout</button>
                            </form>
                        </div>
                    @endauth
                </div>
            </header>

            <main class="page">
                @include('partials.flash')
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
