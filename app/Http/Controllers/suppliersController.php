<?php

namespace App\Http\Controllers;

use App\Http\Controllers\API\BaseController;
use App\Models\suppliers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;

class suppliersController extends BaseController
{
    public function index(): JsonResponse
    {
        $products = suppliers::all();
        return $this->sendResponse($products, 'Suppliers retrieved successfully.');
    }

    public function storeSuppliers(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            "address" => "required",
            "phone" => "required",
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
            $input['imgUrl'] = "";
        }
        $supply = suppliers::create($input);
        $convert = [
            'id' => $supply->id,
            'name' => $supply->name,
            "address" => $supply->address,
            "email" => $supply->email,
            'imgUrl' => $supply->imgUrl,
        ];
        return $this->sendResponse($convert, 'Create Supply successfully.');
    }

    public function updateSuppliers(Request $request, $id)
    {
        $supply = suppliers::find($id);
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            "address" => "required",
            "phone" => "required",
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => 'Validation error', 'messages' => $validator->errors()], 400);
        }
        if (!$supply) {
            return $this->sendError(error: "Supply not found.");
        } else {
            $supply->name = $request->name;
            $supply->address = $request->address;
            $supply->phone = $request->phone;
            $supply->imgUrl = $request->imgUrl;
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
            $input['imgUrl'] = "";
        }
        $supply->update($input);
        $convert = [
            'id' => $supply->id,
            'name' => $supply->name,
            "address" => $supply->address,
            'phone' => $supply->phone,
            'imgUrl' => $customer->imgUrl ?? "",
        ];
        return $this->sendResponse($convert, 'Updated Supplier successfully.');

    }

    public function getSuppliers($id)
    {
        $supply = suppliers::where('id', $id)->first();
        if (!$supply) {
            return $this->sendError("Supply not found");
        }

        // If the product is found, return it
        return $this->sendResponse($supply, "successfully");
    }

    public function deleteSupplier(Request $request)
    {
        $customer = Suppliers::where('id', $request->id)->first();

        if (!$customer) {
            return $this->sendError("Supply not found.");
        }

        $customer->delete();
        return $this->sendResponse($customer, "Record $request->id  has been deleted Successfully");

    }

}
