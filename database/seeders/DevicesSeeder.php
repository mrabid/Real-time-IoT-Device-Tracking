<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB; // Add this import
use Carbon\Carbon; // Add for time handling

class DevicesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Add progress output
        $this->command->info('Starting devices data seeding...');

        for ($i = 0; $i < 100; $i++) {
            DB::table('devices')->insert([
                'temperature' => rand(20, 30),
                'humidity' => rand(40, 60),
                'device_id' => rand(1, 10),
                'created_at' => now()->subMinutes(rand(0, 10080))
            ]);

            // Show progress every 10 records
            if ($i % 10 === 0) {
                $this->command->info("Inserted {$i} records...");
            }
        }

        $this->command->info('Successfully seeded 100 device records!');
    }
}
