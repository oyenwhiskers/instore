KeepTrack Workflow & Concept (Web App)
=====================================

Overview
--------
KeepTrack is a performance tracking system for marketing activations. Promoters submit hourly field data, and management monitors performance, KPIs, and conversions through dashboards. The workflow is role-based and revolves around capturing field activity, validating premium redemptions, and rolling up performance metrics.

Roles
-----
Promoter
- Submits hourly performance reports.
- Views KPI progress in real time.
- Submissions auto-capture time and location.

Management
- Views aggregate and detailed performance dashboards.
- Manages promoter accounts and marketing locations.
- Assigns promoters to locations and tracks KPI achievement.

Core Concepts (Non-Technical)
----------------------------
- Hourly Report: The primary data entry unit from promoters.
- KPI: Targets vs. actuals for sales, engagement, sampling, and premium redemption.
- Location: Marketing outlet tied to promoter submissions.
- Premium Tiers: Gift redemptions tied to minimum sales thresholds.

High-Level User Journey
-----------------------
1. User logs in.
2. System detects role (Promoter or Management).
3. User is routed to the appropriate workspace.

Promoter Workflow
-----------------
1. Login and Landing
  - Promoter lands on a personal dashboard with quick access to reporting and KPI progress.

2. Hourly Report Submission
  - Promoter enters hourly performance data:
    - Products sold (count and selection).
    - Total sales amount.
    - Premiums handed out.
    - Customer engagements.
    - Samplings conducted.
  - Premium redemption entries are validated against sales thresholds:
    - Tier 1: Purchase ≥ RM10.
    - Tier 2: Purchase ≥ RM15.
  - Submission automatically attaches:
    - Timestamp.
    - Current location.
    - Promoter identity and assigned outlet.

3. KPI Progress View
  - Promoter sees targets vs. current progress for:
    - Sales.
    - Engagements.
    - Samplings.
    - Premium redemptions.
  - Status is shown as: On Track, Falling Behind, or Achieved.

4. Submission History
  - Promoter can review past submissions grouped by date and hour.

Management Workflow
-------------------
1. Summary Dashboard
  - High-level view of performance for the day and historical ranges.
  - Metrics include averages and conversions such as:
    - Average engagement per day.
    - Average sampling per day.
    - Average sales per day.
    - Average product sales by product.
    - Premium redemptions by tier.
    - KPI achievement per promoter.

2. Drill-Down Analysis
  - Filter by date, location, or promoter.
  - Inspect hourly submissions for detailed tracking.

3. Promoter Management
  - Create or edit promoter profiles.
  - Assign or reassign promoters to locations.
  - Deactivate promoters when needed.

4. Location Management
  - Create and maintain marketing locations.
  - Each location can be linked to assigned promoters.

5. KPI Oversight
  - Set and monitor KPI targets per promoter.
  - Visual status indicators highlight performance gaps.

Data & Metric Flow (Conceptual)
-------------------------------
1. A promoter submits an hourly report.
2. The system saves the report with time and location context.
3. KPI totals for that promoter are updated based on the new report.
4. Management dashboards update aggregates and conversion metrics.

Validation & Guardrails
-----------------------
- Required fields must be completed before submission.
- Premium redemptions must align with sales thresholds.
- Duplicate hourly submissions should be flagged or prevented.

Web-Based Adaptation Notes (Conceptual)
---------------------------------------
- Promoter interface emphasizes fast data entry with minimal steps.
- Management interface emphasizes dashboards, filters, and drill-down tables.
- Role-based navigation ensures each user sees only relevant tools and data.

Technical Mapping (Laravel Web App)
-----------------------------------
This section maps the concept into Laravel entities, routes, controllers, and UI pages for a responsive web app usable on laptop and mobile.

Core Entities (Models + Tables)
-------------------------------
1. User
  - Purpose: Authentication and role-based access.
  - Role values: promoter, manager.
  - Suggested fields: role, phone, status (active/inactive).

2. Location
  - Purpose: Marketing outlet where promoters operate.
  - Fields: name, address, geo_lat, geo_lng, status.

3. PromoterProfile
  - Purpose: Extra profile data for promoters.
  - Fields: user_id, assigned_location_id, employee_code.

4. HourlyReport
  - Purpose: Primary data entry unit.
  - Fields: promoter_user_id, location_id, report_date, report_hour,
    total_sales_amount, engagements_count, samplings_count, created_at.

5. Product
  - Purpose: Track products in sales mix.
  - Fields: name, sku, is_active.

6. HourlyReportItem
  - Purpose: Per-product quantities in an hourly report.
  - Fields: hourly_report_id, product_id, quantity_sold.

7. PremiumRedemption
  - Purpose: Track premium tiers redeemed.
  - Fields: hourly_report_id, tier (1 or 2), quantity.

8. KpiTarget
  - Purpose: Targets per promoter (daily/weekly/monthly).
  - Fields: promoter_user_id, period_type, period_start,
    target_sales_amount, target_engagements, target_samplings,
    target_premium_tier1, target_premium_tier2.

9. KpiSnapshot (optional)
  - Purpose: Precomputed KPI totals for performance.
  - Fields: promoter_user_id, date, totals for sales, engagements,
    samplings, tier1, tier2.

Key Relationships
-----------------
- User (promoter) hasOne PromoterProfile
- PromoterProfile belongsTo Location (assigned outlet)
- Location hasMany HourlyReport
- HourlyReport belongsTo User (promoter) and Location
- HourlyReport hasMany HourlyReportItem
- HourlyReport hasMany PremiumRedemption
- Product hasMany HourlyReportItem
- User (promoter) hasMany KpiTarget

Routes & Controllers (Web)
--------------------------
Public
- GET / -> marketing landing or login

Auth (Laravel Breeze or similar)
- GET/POST /login
- GET/POST /register (optional for admin only)

Promoter Workspace (role=promoter)
- GET /promoter/dashboard -> PromoterDashboardController@index
- GET /promoter/reports/create -> HourlyReportController@create
- POST /promoter/reports -> HourlyReportController@store
- GET /promoter/reports/history -> HourlyReportController@history
- GET /promoter/kpi -> PromoterKpiController@index

Management Workspace (role=manager)
- GET /management/dashboard -> ManagementDashboardController@index
- GET /management/reports -> ManagementReportController@index
- GET /management/reports/{report} -> ManagementReportController@show
- GET /management/promoters -> PromoterManagementController@index
- GET /management/promoters/{user}/edit -> PromoterManagementController@edit
- PUT /management/promoters/{user} -> PromoterManagementController@update
- GET /management/locations -> LocationController@index
- GET /management/locations/create -> LocationController@create
- POST /management/locations -> LocationController@store
- GET /management/locations/{location}/edit -> LocationController@edit
- PUT /management/locations/{location} -> LocationController@update
- GET /management/kpi-targets -> KpiTargetController@index
- GET /management/kpi-targets/{user}/edit -> KpiTargetController@edit
- PUT /management/kpi-targets/{user} -> KpiTargetController@update

Validation Rules (Server)
-------------------------
- Required fields for HourlyReport: report_date, report_hour,
  total_sales_amount, engagements_count, samplings_count.
- Premium thresholds:
  - Tier 1 redemption allowed only if total_sales_amount >= 10.
  - Tier 2 redemption allowed only if total_sales_amount >= 15.
- Prevent duplicate submissions for same promoter + date + hour.
- Location must match assigned promoter location (or explicitly chosen by manager).

Responsive UI Pages (Blade)
---------------------------
Promoter
- dashboard.blade.php: KPI progress cards + quick submit CTA
- report-create.blade.php: fast-entry form with mobile-friendly inputs
- report-history.blade.php: table/list grouped by date
- kpi.blade.php: targets vs actuals with status badges

Management
- dashboard.blade.php: aggregates + charts + filter bar
- reports-index.blade.php: filters (date, location, promoter) + list
- report-show.blade.php: hourly details + items + premiums
- promoters-index.blade.php: list + assign location
- locations-index.blade.php: CRUD list
- kpi-targets.blade.php: per-promoter target editor

Access Control
--------------
- Use middleware to restrict routes by role:
  - promoter routes: role=promoter
  - management routes: role=manager

Data Flow (Technical)
---------------------
1. Promoter submits hourly report form.
2. Controller validates data and premium thresholds.
3. Record saved to HourlyReport + items + premiums.
4. KPI totals calculated on the fly or stored in KpiSnapshot.
5. Management dashboards query aggregates by date/location/promoter.
