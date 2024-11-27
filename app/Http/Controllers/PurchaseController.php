<?php

namespace App\Http\Controllers;

use App\Http\Controllers\API\BaseController;
use App\Models\Products;
use App\Models\Purchase;
use App\Models\Status;
use App\Models\suppliers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PurchaseController extends BaseController
{
    public function index()
    {
        $purchase = Purchase::all();
        return $this->sendResponse($purchase, "retrieve data successfully");
    }

    public function storeProduct(Request $request)
    {
        $product = Products::where("id", $request->product)->first();
        if (!$product) {
            return $this->sendError("product not found", 404);
        }
        $supplier = suppliers::where("id", $request->supplier)->first();
        if (!$supplier) {
            return $this->sendError("supplier not found", 404);
        }
        $status = Status::where("id", $request->status)->first();
        if (!$status) {
            return $this->sendError("status not found", 404);
        }
        $validator = Validator::make($request->all(), [
            'product' => 'required',
            "quantity" => "required",
            "supplier" => "required",
            "date" => "required",
            'status' => 'required',
            "other" => 'nullable',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => 'Validation error', 'messages' => $validator->errors()], 400);
        }
        $input = $request->all();
        $input['status'] = $status->status;
        $purchase = Purchase::create($input);
        $product->quantity += $request->quantity;

        $product->save();
        $convert = [
            'id' => $purchase->id,
            'product' => $purchase->product,
            "quantity" => $purchase->quantity,
            "supplier" => $purchase->supplier,
            "date" => $purchase->date,
            'status' => $purchase->status,
            'other' => $purchase->other,
        ];
        return $this->sendResponse($convert, 'Create purchase successfully.');
    }

    public function getDetailPurchase(Request $request)
    {
        $purchase = Purchase::where("id", $request->id)->first();

        if (!$purchase) {
            return $this->sendError("Purchase not found", 404);
        }
        return $this->sendResponse($purchase, "Retrieved purchase with ID $purchase->id successfully.");
    }
}
