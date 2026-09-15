<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@courier.test',
            'phone' => '+1234567890',
            'password' => Hash::make('password'),
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_ACTIVE,
            'address' => '123 Admin St',
            'city' => 'New York',
            'state' => 'NY',
            'postal_code' => '10001',
        ]);

        // Create delivery personnel
        for ($i = 1; $i <= 5; $i++) {
            User::create([
                'name' => "Delivery Person {$i}",
                'email' => "delivery{$i}@courier.test",
                'phone' => "+123456789{$i}",
                'password' => Hash::make('password'),
                'role' => User::ROLE_DELIVERY,
                'status' => User::STATUS_ACTIVE,
                'address' => "{$i}00 Delivery Ave",
                'city' => 'New York',
                'state' => 'NY',
                'postal_code' => "1000{$i}",
            ]);
        }

        // Create customers
        for ($i = 1; $i <= 10; $i++) {
            User::create([
                'name' => "Customer {$i}",
                'email' => "customer{$i}@courier.test",
                'phone' => "+198765432{$i}",
                'password' => Hash::make('password'),
                'role' => User::ROLE_CUSTOMER,
                'status' => User::STATUS_ACTIVE,
                'address' => "{$i}00 Customer St",
                'city' => 'Los Angeles',
                'state' => 'CA',
                'postal_code' => "9000{$i}",
            ]);
        }

        $this->call([
            ShipmentSeeder::class,
            PaymentSeeder::class,
            DeliverySeeder::class,
        ]);
    }
}
