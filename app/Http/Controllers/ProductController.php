<?php

namespace App\Http\Controllers;


use App\Http\Controllers\API\BaseController;
use App\Models\Products;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

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
                    $file->move(public_path('images'), $filename);

                    $input['imgUrl'] = 'images/' . $filename;

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

        if ($request->file('imgUrl')) {
            $file = $request->file('imgUrl');

            $extension = $file->getClientOriginalExtension();
            if (in_array($extension, ['jpeg', 'png', 'jpg', 'gif', 'svg'])) {
                if ($file->getSize() <= 2 * 1024 * 1024 && $file->getSize() == true) {
                    $filename = time() . '.' . $extension;
                    $file->move(public_path('images'), $filename);

                    $input['imgUrl'] = 'images/' . $filename;

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
}
