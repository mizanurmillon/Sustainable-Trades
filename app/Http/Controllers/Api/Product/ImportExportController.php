<?php

namespace App\Http\Controllers\Api\Product;

use App\Exports\ProductExport;
use App\Http\Controllers\Controller;
use App\Imports\ProductImport;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class ImportExportController extends Controller
{
    use ApiResponse;
    
    public function importProducts(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "file"=> "required|file|mimetypes:text/plain,text/csv,application/vnd.ms-excel,application/csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet|max:5120", // 5MB max size
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(),$validator->errors()->first(), 422);
        }

        $user = auth()->user();

        if (!$user) {
            return $this->error([], 'User not found', 401);   
        }

       $data = Excel::import(new ProductImport($user->shopInfo->id), $request->file('file'));

        if (!$data) {
            return $this->error([], 'Some rows failed to import', 422);
        }

        return $this->success($data, 'Products imported successfully', 200);
    }

    public function exportProducts(Request $request, $id)
    {
        $user = auth()->user();

        if (!$user) {

            return $this->error([], 'User not found', 401);   
        }

        return Excel::download(new ProductExport($id), 'products.csv');

        // return $this->success([], 'Products exported successfully', 200);

    }
}
