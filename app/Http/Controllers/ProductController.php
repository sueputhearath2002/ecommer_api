<?php

namespace App\Http\Controllers;


use App\Http\Controllers\API\BaseController;
use App\Models\outgoing;
use App\Models\Products;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductController extends BaseController
{
    public function index(): JsonResponse
    {
        $products = Products::all();
        return $this->sendResponse($products, 'Products retrieved successfully.');
    }

    public function storeProduct(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            "price" => "required",
            "quantity" => "required",
            "categories" => "required",
            'detail' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => 'Validation error', 'messages' => $validator->errors()], 400);
        }
        $input = $request->all();
        if ($request->file('imgUrl')) {
            $file = $request->file('imgUrl');

            $extension = $file->getClientOriginalExtension();
            if (in_array($extension, ['jpeg', 'png', 'jpg', 'gif', 'svg'])) {
                if ($file->getSize() <= 2 * 1024 * 1024 && $file->getSize() == true) {
                    $filename = time() . '.' . $extension;
                    $file->move(public_path('images/products'), $filename);

                    $input['imgUrl'] = url('images/products/' . $filename);

                } else {
                    return $this->sendError("File is too large. Maximum size is 2MB.");
                }
            } else {
                return $this->sendError("Invalid image type. Allowed types: jpeg, png, jpg, gif, svg.");
            }

        } else {
            return $this->sendError("No file uploaded.");
        }
        $product = Products::create($input);
        $convert = [
            'id' => $product->id,
            'name' => $product->name,
            "price" => $product->price,
            "quantity" => $product->quantity,
            "categories" => $product->categories,
            'detail' => $product->detail,
            'imgUrl' => $product->imgUrl,
        ];
        return $this->sendResponse($convert, 'Create Products successfully.');
    }

    public function getProducts($id)
    {
        $product = Products::where('id', $id)->first();
        if (!$product) {
            return $this->sendError("Product not found");
        }

        // If the product is found, return it
        return $this->sendResponse($product, "successfully");
    }
    public function updateProduct(Request $request, $id)
    {
        $product = Products::find($id);
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            "price" => "required",
            "quantity" => "required",
            "categories" => "required",
            'detail' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => 'Validation error', 'messages' => $validator->errors()], 400);
        }
        if (!$product) {
            return $this->sendError("Product not found.");
        } else {
            $product->name = $request->name;
            $product->detail = $request->detail;
            $product->imgUrl = $request->imgUrl;
        }


        $input = $request->all();
        //ok
        if ($request->file('imgUrl')) {
            $file = $request->file('imgUrl');

            $extension = $file->getClientOriginalExtension();
            if (in_array($extension, ['jpeg', 'png', 'jpg', 'gif', 'svg'])) {
                if ($file->getSize() <= 2 * 1024 * 1024 && $file->getSize() == true) {
                    $filename = time() . '.' . $extension;
                    $file->move(public_path('images/products'), $filename);

                    $input['imgUrl'] = url('images/products/' . $filename);

                } else {
                    return $this->sendError("File is too large. Maximum size is 2MB.");
                }
            } else {
                return $this->sendError("Invalid image type. Allowed types: jpeg, png, jpg, gif, svg.");
            }

        } else {

            return $this->sendError("No file uploaded.");
        }
        $product->update($input);
        $convert = [
            'id' => $product->id,
            'name' => $product->name,
            "price" => $product->price,
            "quantity" => $product->quantity,
            "categories" => $product->categories,
            'detail' => $product->detail,
            'imgUrl' => $product->imgUrl,
        ];
        return $this->sendResponse($convert, 'Updated Products successfully.');

    }

    public function deleteProduct(Request $request)
    {
        $product = Products::where('id', $request->id)->first();

        if (!$product) {
            return $this->sendError("Product not found.");
        }

        $product->delete();
        return $this->sendResponse($product, "Record $request->id  has been deleted Successfully");

    }

    public function getWeeklySales()
    {
        $startOfWeek = Carbon::now()->startOfWeek()->toDateString();
        $endOfWeek = Carbon::now()->endOfWeek()->toDateString();


        $sales = outgoing::whereRaw('DATE(date) BETWEEN ? AND ?', [$startOfWeek, $endOfWeek])
            ->whereNotNull('quantity')  // Ensure quantity is not null
            ->where('quantity', '>', 0) // Optionally filter out 0 quantity records
            ->selectRaw("DATE_FORMAT(date, '%d-%m-%Y') as date, SUM(quantity) as total_quantity")
            ->groupBy('date')  // Group by raw date (Y-m-d format)
            ->orderBy('date')
            ->get();

        $dateRange = Carbon::parse($startOfWeek)->daysUntil($endOfWeek);
        $formattedData = collect();

        foreach ($dateRange as $date) {
            $formattedDate = $date->format('d-m-Y');
            $saleForDate = $sales->firstWhere('date', $formattedDate);

            $formattedData->push([
                'date' => $formattedDate,
                'total_quantity' => $saleForDate ? $saleForDate->total_quantity : 0, // Corrected this line
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $formattedData,
        ]);
    }
}
