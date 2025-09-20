<?php

namespace Database\Seeders;

use Carbon\Carbon;
use App\Models\HowItWork;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class HowItWorkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        HowItWork::insert([
            [
                'title' => 'Buy',
                'description' => 'Explore local goods and services sorted by geographical proximity. Coordinate pickup or shipping with sellers.',
                'image' => 'backend/images/Icons - Graphic Elements (2).png',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'Sell',
                'description' => 'Got sustainable goods or badass skills? Join Sustainable Trades, get unlimited listings! Members pay an annual fee and keep 100% of their cash sales.',
                'image' => 'backend/images/Icons - Graphic Elements (3).png',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'Trade',
                'description' => 'Members can barter and trade for quality goods and services, how fun!',
                'image' => 'backend/images/Graphic Element Frame (2).png',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            
        ]);
    }
}
