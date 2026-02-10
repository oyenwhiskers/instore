<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Plan;
use App\Models\BrandClient;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $adminIc = '900101011111';
        $adminEmail = 'admin@keeptrack.test';

        User::firstOrCreate(
            ['ic_number' => $adminIc],
            [
                'name' => 'System Admin',
                'email' => $adminEmail,
                'password' => Hash::make('Admin@1234'),
                'role' => 'manager',
                'status' => 'active',
            ]
        );

        $planId = Plan::where('name', 'Essential')->value('id');

        $company = Company::firstOrCreate(
            ['name' => 'Demo Marketing Co'],
            [
                'status' => 'active',
                'billing_cycle' => 'monthly',
                'subscription_ends_at' => now()->addMonth(),
                'plan_id' => $planId,
            ]
        );

        User::firstOrCreate(
            ['email' => 'customer@keeptrack.test'],
            [
                'name' => 'Customer Admin',
                'email' => 'customer@keeptrack.test',
                'password' => Hash::make('Customer@1234'),
                'role' => 'customer_admin',
                'status' => 'active',
                'company_id' => $company->id,
            ]
        );

        $brandSeed = [
            'Coca Cola' => [
                '1.5L Vanilla Coke',
                '500ml Tin Coke',
                'Coke Zero 500ml',
                'Sprite 1.5L',
                'Fanta Orange 500ml',
                'Coke Light 320ml',
            ],
            'Nestle' => [
                'Milo 1L',
                'Nescafe Latte Can',
                'KitKat 4 Finger',
                'Milo Nuggets',
                'Nestea Lemon 500ml',
            ],
            'Unilever' => [
                'Dove Body Wash',
                'Lux Bar Soap',
                'Sunsilk Shampoo',
                'Lipton Iced Tea 500ml',
            ],
            'PepsiCo' => [
                'Pepsi 500ml',
                'Mountain Dew 500ml',
                '7UP 1.5L',
                'Mirinda Orange 500ml',
            ],
        ];

        $targetCounts = [
            'Coca Cola' => 17,
            'Nestle' => 14,
            'Unilever' => 10,
            'PepsiCo' => 12,
        ];

        foreach ($brandSeed as $brandName => $productNames) {
            $brandClient = BrandClient::firstOrCreate(
                ['company_id' => $company->id, 'name' => $brandName],
                ['status' => 'active']
            );

            $targetCount = $targetCounts[$brandName] ?? count($productNames);
            $baseSku = Str::upper(Str::slug($brandName, ''));

            $index = 1;
            foreach ($productNames as $productName) {
                Product::firstOrCreate(
                    [
                        'company_id' => $company->id,
                        'brand_client_id' => $brandClient->id,
                        'name' => $productName,
                    ],
                    [
                        'sku' => $baseSku . str_pad((string) $index, 3, '0', STR_PAD_LEFT),
                        'is_active' => true,
                    ]
                );
                $index++;
            }

            while ($index <= $targetCount) {
                $productName = $brandName . ' Product ' . $index;
                Product::firstOrCreate(
                    [
                        'company_id' => $company->id,
                        'brand_client_id' => $brandClient->id,
                        'name' => $productName,
                    ],
                    [
                        'sku' => $baseSku . str_pad((string) $index, 3, '0', STR_PAD_LEFT),
                        'is_active' => true,
                    ]
                );
                $index++;
            }
        }

        // Seed demo events, premiums, and assignments before hourly reports
        $this->call(EventDemoSeeder::class);
        $this->call(HourlyReportSeeder::class);
    }
}
