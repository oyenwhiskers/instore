<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\EventStockMovement;
use App\Models\HourlyReport;
use App\Models\HourlyReportItem;
use App\Models\PremiumRedemption;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class HourlyReportSeeder extends Seeder
{
    public function run(): void
    {
        $events = Event::with(['promoters', 'products', 'premiums'])->get();
        
        if ($events->isEmpty()) {
            return;
        }

        $initialStocks = [
            'vanilla' => 100,
            'tin' => 500,
        ];

        foreach ($events as $event) {
            if ($event->promoters->isEmpty() || $event->products->isEmpty()) {
                continue;
            }

            $promoters = $event->promoters;
            $products = $event->products;
            $premiums = $event->premiums;
            $startDate = $event->start_date;
            $endDate = $event->end_date;
            $initialStockUserId = $event->created_by;

            $vanillaProduct = $products->first(function ($product) {
                return stripos((string) $product->name, 'vanilla') !== false;
            });

            $tinProduct = $products->first(function ($product) {
                return stripos((string) $product->name, 'tin') !== false;
            });

            foreach ([
                $vanillaProduct ? [$vanillaProduct, $initialStocks['vanilla']] : null,
                $tinProduct ? [$tinProduct, $initialStocks['tin']] : null,
            ] as $seed) {
                if (!$seed) {
                    continue;
                }

                [$product, $quantity] = $seed;

                $existingInitial = EventStockMovement::where('event_id', $event->id)
                    ->where('product_id', $product->id)
                    ->where('movement_type', 'in')
                    ->where('notes', 'Initial stock')
                    ->exists();

                if (!$existingInitial) {
                    EventStockMovement::create([
                        'event_id' => $event->id,
                        'product_id' => $product->id,
                        'movement_type' => 'in',
                        'quantity' => $quantity,
                        'notes' => 'Initial stock',
                        'created_by' => $initialStockUserId,
                    ]);
                }
            }

            $currentDate = Carbon::parse($startDate)->startOfDay();
            $endDate = Carbon::parse($endDate)->startOfDay();

            while ($currentDate <= $endDate) {
                for ($hour = 8; $hour < 17; $hour++) {
                    foreach ($promoters as $promoter) {
                        if (rand(1, 100) <= 80) {
                            $reportDate = $currentDate->toDateString();

                            $report = HourlyReport::where('promoter_user_id', $promoter->id)
                                ->whereDate('report_date', $reportDate)
                                ->where('report_hour', $hour)
                                ->first();

                            $createdNewReport = false;

                            if (!$report) {
                                $salesAmount = rand(50, 300);
                                $engagements = rand(3, 15);
                                $samplings = rand(2, 10);

                                $report = HourlyReport::create([
                                    'promoter_user_id' => $promoter->id,
                                    'location_id' => $event->location_id,
                                    'report_date' => $reportDate,
                                    'report_hour' => $hour,
                                    'total_sales_amount' => $salesAmount,
                                    'engagements_count' => $engagements,
                                    'samplings_count' => $samplings,
                                ]);

                                $createdNewReport = true;
                            }

                            if ($createdNewReport && $products->isNotEmpty()) {
                                $productsToAdd = $products->random(rand(1, min(3, $products->count())));
                                foreach ($productsToAdd as $product) {
                                    $quantitySold = rand(1, 5);

                                    HourlyReportItem::create([
                                        'hourly_report_id' => $report->id,
                                        'product_id' => $product->id,
                                        'quantity_sold' => $quantitySold,
                                    ]);

                                    EventStockMovement::create([
                                        'event_id' => $event->id,
                                        'product_id' => $product->id,
                                        'movement_type' => 'out',
                                        'quantity' => $quantitySold,
                                        'notes' => 'Stock out: ' . $promoter->name . ' sold ' . $quantitySold,
                                        'created_by' => $promoter->id,
                                    ]);
                                }
                            }

                            $hasPremiums = $report->premiums()->whereNotNull('premium_id')->exists();

                            if (!$hasPremiums && $premiums->isNotEmpty() && rand(1, 100) <= 50) {
                                $premiumsToAdd = $premiums->random(rand(1, min(2, $premiums->count())));
                                foreach ($premiumsToAdd as $premium) {
                                    PremiumRedemption::create([
                                        'hourly_report_id' => $report->id,
                                        'premium_id' => $premium->id,
                                        'tier' => 1,
                                        'quantity' => rand(1, 3),
                                    ]);
                                }
                            }
                        }
                    }
                }

                $currentDate->addDay();
            }
        }
    }
}
