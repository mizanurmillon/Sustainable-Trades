<?php

namespace Database\Seeders;

use App\Models\Banner;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BannerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Banner::insert([
            [
                'title' => 'EAT, TRADE, SELL, AND BUY LOCALLY',
                'sub_title' => 'Welcome to Sustainable Trades',
                'description' => 'Your destination for trading, selling, eating, and buying local, organic, and sustainable goods and services. Join our eco-conscious community, where connections go beyond transactions.',
                'image' => 'backend/images/sliderOne.webp',
                'status' => 'active',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'MAGIC MAKERS LOCALIZED GLOBALLY',
                'sub_title' => 'Are You a Magic Maker?',
                'description' => 'A Magic Maker is someone who offers products or services that benefit people or the planet. They include organic farmers, gardeners, local artists, and entrepreneurs supporting sustainability.',
                'image' => 'backend/images/sliderTwo.webp',
                'status' => 'active',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'OUR MISSION AND FUTURE VISION',
                'sub_title' => 'Sustainable Trades Farms',
                'description' => 'Membership fees support the creation of Sustainable Trades Farms, dedicated to fair compensation, work-life balance, land regeneration, and wholesome food.',
                'image' => 'backend/images/sliderThree.webp',
                'status' => 'active',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            
        ]);
    }
}
