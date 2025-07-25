<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\SubCategory;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategoryTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Category::insert([
            [
                'name' => 'Farm to Table',
                'image' => 'backend/images/Vector.png',
                'status' => 'active',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Arts & Artisans',
                'image' => 'backend/images/Vector (1).png',
                'status' => 'active',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Bath & Beauty',
                'image' => 'backend/images/Vector (1).png',
                'status' => 'active',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Books & Literature',
                'image' => 'backend/images/Vector (3).png',
                'status' => 'active',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Healing & Wellness',
                'image' => 'backend/images/Group.png',
                'status' => 'active',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
           
            [
                'name' => 'Community Events',
                'image' => 'backend/images/Vector (4).png',
                'status' => 'active',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Services',
                'image' => 'backend/images/Group (1).png',
                'status' => 'active',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);

        SubCategory::insert([
            [
                'sub_category_name' => 'Acupuncture',
                'category_id' => 1,
                'status' => 'active',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'sub_category_name' => 'Akashic Record',
                'category_id' => 2,
                'status' => 'active',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'sub_category_name' => 'Coaching',
                'category_id' => 3,
                'status' => 'active',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'sub_category_name' => 'Cranial Sacral',
                'category_id' => 4,
                'status' => 'active',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'sub_category_name' => 'Qi Gong',
                'category_id' => 5,
                'status' => 'active',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'sub_category_name' => 'Somatic Practices',
                'category_id' => 5,
                'status' => 'active',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'sub_category_name' => 'Trauma Resolution',
                'category_id' => 5,
                'status' => 'active',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'sub_category_name' => 'Yoga',
                'category_id' => 5,
                'status' => 'active',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'sub_category_name' => 'Somatic Practices',
                'category_id' => 5,
                'status' => 'active',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'sub_category_name' => 'Reiki',
                'category_id' => 6,
                'status' => 'active',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'sub_category_name' => 'Sound/Light Healing Therapy',
                'category_id' => 7,
                'status' => 'active',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'sub_category_name' => 'Hypnosis',
                'category_id' => 6,
                'status' => 'active',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'sub_category_name' => 'Chiropractic',
                'category_id' => 7,
                'status' => 'active',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'sub_category_name' => 'Yoga',
                'category_id' => 1,
                'status' => 'active',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'sub_category_name' => 'Somatic Practices',
                'category_id' => 1,
                'status' => 'active',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'sub_category_name' => 'Reiki',
                'category_id' => 2,
                'status' => 'active',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'sub_category_name' => 'Sound/Light Healing Therapy',
                'category_id' => 2,
                'status' => 'active',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'sub_category_name' => 'Hypnosis',
                'category_id' => 3,
                'status' => 'active',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'sub_category_name' => 'Chiropractic',
                'category_id' => 3,
                'status' => 'active',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'sub_category_name' => 'Reiki',
                'category_id' => 2,
                'status' => 'active',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'sub_category_name' => 'Sound/Light Healing Therapy',
                'category_id' => 4,
                'status' => 'active',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'sub_category_name' => 'Hypnosis',
                'category_id' => 4,
                'status' => 'active',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'sub_category_name' => 'Chiropractic',
                'category_id' => 6,
                'status' => 'active',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'sub_category_name' => 'Hypnosis',
                'category_id' => 7,
                'status' => 'active',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'sub_category_name' => 'Chiropractic',
                'category_id' => 7,
                'status' => 'active',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
