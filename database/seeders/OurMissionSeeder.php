<?php

namespace Database\Seeders;

use App\Models\OurMissoin;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class OurMissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         OurMissoin::insert([
            [
                'name' => 'Encourage Individualism',
                'description' =>'Each individual has the power to make a positive impact on the world. Encouraging individuals’ skills, passions, and services with intention strengthens the systemic change needed for our planet.',
                'image' => 'backend/images/Graphic Elements.png',
                'status' => 'active',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Support Local Businesses',
                'description' =>'By supporting local businesses we shift the authority away from corporations back to the people in our community. We promote diversity, transparency, responsibility, and autonomy, reducing dependence on global supply chains.',
                'image' => 'backend/images/Graphic Element Frame.png',
                'status' => 'active',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Shorten the Supply Chain',
                'description' =>'Local, biodiverse farming cultivates living soil, provides wildlife habitats, ensures community food security, creates healthy jobs connecting people with nature, and yields nutritious food. This shortens the supply chain, reducing packaging and preservatives.',
                'image' => 'backend/images/Icons - Graphic Elements (1).png',
                'status' => 'active',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Strengthen Our Connection to the Earth',
                'description' =>"The End Goal for Sustainable Trades is to create Sustainable Trades Farms built using permaculture, natural building, and renewable energy.  This system fosters community, allows us to slow down, and reconnect with nature. Nature's wisdom heals when we connect with its intelligence.",
                'image' => 'backend/images/Graphic Element Frame (1).png',
                'status' => 'active',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
