<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Event;
use App\Models\Location;
use App\Models\Premium;
use App\Models\Product;
use App\Models\PromoterAssignment;
use App\Models\PromoterProfile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class EventDemoSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();
        $manager = User::where('role', 'manager')->first();

        if (!$company || !$manager) {
            return;
        }

        $locations = collect([
            ['name' => 'Sunway Pyramid Mall', 'address' => '3, Jalan PJS 11/15, Bandar Sunway'],
            ['name' => 'Mid Valley Megamall', 'address' => 'Lingkaran Syed Putra, Kuala Lumpur'],
            ['name' => 'IOI City Mall', 'address' => 'Lebuh IRC, Putrajaya'],
        ])->map(function ($data) {
            return Location::firstOrCreate(
                ['name' => $data['name']],
                ['address' => $data['address'], 'status' => 'active']
            );
        });

        $promoterSeeds = [
            ['name' => 'William Tan', 'email' => 'william.promoter@keeptrack.test', 'ic' => '901212011111'],
            ['name' => 'Nur Aina', 'email' => 'aina.promoter@keeptrack.test', 'ic' => '920311022222'],
            ['name' => 'Jason Lim', 'email' => 'jason.promoter@keeptrack.test', 'ic' => '930415033333'],
            ['name' => 'Farah Aziz', 'email' => 'farah.promoter@keeptrack.test', 'ic' => '940210044444'],
            ['name' => 'Daniel Koh', 'email' => 'daniel.promoter@keeptrack.test', 'ic' => '950815055555'],
            ['name' => 'Hannah Lee', 'email' => 'hannah.promoter@keeptrack.test', 'ic' => '960622066666'],
        ];

        $promoters = collect($promoterSeeds)->map(function ($seed, $index) use ($company) {
            $user = User::firstOrCreate(
                ['email' => $seed['email']],
                [
                    'name' => $seed['name'],
                    'email' => $seed['email'],
                    'password' => Hash::make('Promoter@1234'),
                    'role' => 'promoter',
                    'status' => 'active',
                    'company_id' => $company->id,
                    'ic_number' => $seed['ic'],
                ]
            );

            PromoterProfile::firstOrCreate(
                ['user_id' => $user->id],
                ['employee_code' => 'PRM-' . str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT)]
            );

            return $user;
        });

        $premiumSeeds = [
            ['gift_name' => 'Limited Edition Tote Bag', 'mechanic_description' => 'Redeem with RM10 sales'],
            ['gift_name' => 'Collector Keychain', 'mechanic_description' => 'Redeem with 2 product purchases'],
            ['gift_name' => 'Merchandise Voucher RM5', 'mechanic_description' => 'Redeem with sampling + purchase'],
        ];

        $premiums = collect($premiumSeeds)->map(function ($seed) use ($company, $manager) {
            return Premium::firstOrCreate(
                ['gift_name' => $seed['gift_name'], 'company_id' => $company->id],
                [
                    'created_by' => $manager->id,
                    'mechanic_description' => $seed['mechanic_description'],
                ]
            );
        });

        $products = Product::where('company_id', $company->id)->get();
        if ($products->isEmpty()) {
            return;
        }

        $today = Carbon::today();

        $eventSeeds = [
            [
                'name' => 'Coca-Cola Sunway Launch',
                'status' => 'active',
                'start' => $today->copy()->subDays(3),
                'end' => $today->copy()->addDays(3),
                'location' => $locations->get(0),
                'promoters' => $promoters->slice(0, 3),
            ],
            [
                'name' => 'Nestle Weekend Roadshow',
                'status' => 'upcoming',
                'start' => $today->copy()->addDays(5),
                'end' => $today->copy()->addDays(9),
                'location' => $locations->get(1),
                'promoters' => $promoters->slice(3, 3),
            ],
            [
                'name' => 'Unilever City Mall Wrap-Up',
                'status' => 'completed',
                'start' => $today->copy()->subDays(15),
                'end' => $today->copy()->subDays(10),
                'location' => $locations->get(2),
                'promoters' => $promoters->slice(1, 4),
            ],
        ];

        foreach ($eventSeeds as $seed) {
            if (!$seed['location']) {
                continue;
            }

            $event = Event::firstOrCreate(
                ['name' => $seed['name']],
                [
                    'company_id' => $company->id,
                    'location_id' => $seed['location']->id,
                    'created_by' => $manager->id,
                    'status' => $seed['status'],
                    'start_date' => $seed['start']->toDateString(),
                    'end_date' => $seed['end']->toDateString(),
                    'notes' => 'Demo event seeded for client presentation.',
                ]
            );

            $selectedProducts = $products->random(min(10, $products->count()));
            $eventProductSync = [];
            foreach ($selectedProducts as $product) {
                $eventProductSync[$product->id] = [
                    'unit_price' => rand(3, 12),
                ];
            }
            $event->products()->sync($eventProductSync);
            $seed['location']->products()->syncWithoutDetaching($selectedProducts->pluck('id')->all());

            $event->premiums()->sync($premiums->random(min(2, $premiums->count()))->pluck('id')->all());

            $promoterSync = [];
            foreach ($seed['promoters'] as $promoter) {
                $promoterSync[$promoter->id] = [
                    'start_date' => $seed['start']->toDateString(),
                    'end_date' => $seed['end']->toDateString(),
                    'start_time' => '10:00',
                    'end_time' => '18:00',
                    'notes' => 'Seeded assignment.',
                ];

                PromoterAssignment::updateOrCreate(
                    ['user_id' => $promoter->id],
                    [
                        'location_id' => $seed['location']->id,
                        'start_date' => $seed['start']->toDateString(),
                        'end_date' => $seed['end']->toDateString(),
                        'start_time' => '10:00',
                        'end_time' => '18:00',
                        'notes' => 'Seeded assignment.',
                    ]
                );
            }
            $event->promoters()->sync($promoterSync);
        }
    }
}
