<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductExport implements FromCollection, WithHeadingRow
{
    /**
    * @return \Illuminate\Support\Collection
    */
    protected $id;

    public function __construct($id)
    {
        $this->id = $id;
    }
    public function collection()
    {
        return Product::where('id',$this->id)->get();
    }
    public function headings(): array
    {
        return [
            'id',
            'shop_info_id',
            'product_name',
            'product_price',
            'product_quantity',
            'weight',
            'cost',
            'unlimited_stock',
            'out_of_stock',
            'video',
            'description',
            'category_id',
            'sub_category_id',
            'fulfillment',
            'selling_option',
            'tag',
            'product_images',
            'is_featured',
        ];
    }
}
