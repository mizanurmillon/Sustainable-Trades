<?php

namespace App\Imports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Validator;

class ProductImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */

    protected $shopId;

    public function __construct($shopId)
    {
        $this->shopId = $shopId;
    }

    public function model(array $row)
    {
        // Validate each row
        $validator = Validator::make($row, [
            'product_name'       => 'required|string|max:255',
            'product_price'      => 'required|numeric|min:0',
            'product_quantity'   => 'required|numeric|min:1',
            'weight'             => 'nullable|numeric|min:0',
            'cost'               => 'nullable|numeric|min:0',
            'unlimited_stock'    => 'nullable|boolean',
            'out_of_stock'       => 'nullable|boolean',
            'video'              => 'nullable|string|max:255',
            'description'        => 'required|string',
            'category_id'        => 'required|exists:categories,id',
            'sub_category_id'    => 'required|exists:sub_categories,id',
            'fulfillment'        => 'required|string|max:255',
            'selling_option'     => 'required|string|max:255',
            'tags'               => 'nullable|string',
            'product_images'     => 'nullable|string',
            'is_featured'        => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            // Skip invalid row, or log it
            return null;
        }

        // Create product
        $product = new Product([
            'shop_info_id'     => $this->shopId,
            'product_name'     => $row['product_name'],
            'product_price'    => $row['product_price'],
            'product_quantity' => $row['product_quantity'],
            'weight'           => $row['weight'],
            'cost'             => $row['cost'],
            'unlimited_stock'  => $row['unlimited_stock'] ?? false,
            'out_of_stock'     => $row['out_of_stock'] ?? false,
            'video'            => $row['video'] ?? null,
            'description'      => $row['description'],
            'category_id'      => $row['category_id'],
            'sub_category_id'  => $row['sub_category_id'],
            'fulfillment'      => $row['fulfillment'],
            'selling_option'   => $row['selling_option'],
            'is_featured'      => $row['is_featured'] ?? false,
            'status'           => 'listing',
        ]);

        
        // Delay saving related data until saved
        $product->save();

        // Save tags (comma-separated)
        if (!empty($row['tags'])) {
            $tags = explode(',', $row['tags']);
            foreach ($tags as $tag) {
                $product->metaTags()->create([
                    'tag' => trim($tag),
                ]);
            }
        } 

        // Save images (comma-separated)
        if (!empty($row['product_images'])) {
            $images = explode(',', $row['product_images']);
            foreach ($images as $image) {
                $product->images()->create([
                    'image' => trim($image),
                ]);
            }
        }

        return $product;
    }
}
