<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class SystemSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SystemSetting::insert([
            [
                'id'             => 1,
                'title'          => 'Sustainable Trades Org',
                'email'          => 'support@gmail.com',
                'system_name'    => 'Sustainable Trades Org',
                'copyright_text' => '©sustainabletrades.org 2023, All right reserved.',
                'logo'           => 'backend/images/g10.png',
                'favicon'        => 'backend/images/g10.png',
                'description'    => 'The Description',
                'created_at'     => Carbon::now(),
            ],
        ]);
    }
}
