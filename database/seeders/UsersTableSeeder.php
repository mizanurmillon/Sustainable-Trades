<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'first_name' => 'Admin',
                'last_name'=> null,
                'email' => 'admin@admin.com',
                'is_premium' => 0,
                'email_verified_at' => now(),
                'password' => Hash::make('12345678'),
                'role' => 'admin',
                'remember_token' => Str::random(10),
                'created_at' => now(),
            ],
            [
                'first_name' => 'Customer',
                'last_name'=> null,
                'email' => 'customer@customer.com',
                'is_premium' => 0,
                'email_verified_at' => now(),
                'password' => Hash::make('12345678'),
                'role' => 'customer',
                'remember_token' => Str::random(10),
                'created_at' => now(),
            ],
            [
                'first_name' => 'Vendor',
                'last_name'=> null,
                'email' => 'vendor@vendor.com',
                'is_premium' => 0,
                'email_verified_at' => now(),
                'password' => Hash::make('12345678'),
                'role' => 'vendor',
                'remember_token' => Str::random(10),
                'created_at' => now(),
            ],
            [
                'first_name' => 'Md Mizanur',
                'last_name'=> 'Rahman',
                'email' => 'mr7517218@gmail.com',
                'is_premium' => 0,
                'email_verified_at' => now(),
                'password' => Hash::make('12345678'),
                'role' => 'vendor',
                'remember_token' => Str::random(10),
                'created_at' => now(),
            ],
        ]);
    }
}
