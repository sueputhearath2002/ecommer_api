<?php

namespace App\Http\Controllers;

use App\Http\Controllers\API\BaseController;
use App\Models\Customer;
use App\Models\outgoing;
use App\Models\Products;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class outgoingController extends BaseController
{

    public function index(): JsonResponse
    {
        $out = outgoing::all()->map(function ($item) {
            return [
                'id' => $item->id,
                'product' => $item->product,
                'quantity' => $item->quantity,
                'customer' => $item->customer,
                'date' => \Carbon\Carbon::parse($item->date)->format('d-m-Y'),
            ];
        });
        return $this->sendResponse($out, 'Outgoing retrieved successfully.');
    }

    public function storeOutgoing(Request $request)
    {

        $checkProduct = Products::where('id', $request->product)->first();
        if (!$checkProduct) {
            return $this->sendError('Product not found', );
        }
        $checkCustomer = Customer::where('id', $request->customer)->first();
        if (!$checkCustomer) {
            return $this->sendError('Customer not found', );
        }


        $validator = Validator::make($request->all(), [
            'product' => 'required',
            "quantity" => "required",
            "customer" => "required",
            "date" => 'nullable|date',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => 'Validation error', 'messages' => $validator->errors()], 400);
        }
        $input = $request->all();
        $input['date'] = $input['date'] ?? Carbon::now()->toDateString();
        $input["product"] = $checkProduct->id;
        $getQty = 0;
        $getQty = $checkProduct->quantity - $request->quantity;
        if ($getQty < 0) {
            return $this->sendError('Quantity is not available', );
        } else {
            $checkProduct->quantity = $getQty;
            $checkProduct->save();
            $out = outgoing::create($input);
            $convert = [
                'id' => $out->id,
                'product' => $out->product,
                "quantity" => $out->quantity,
                "customer" => $out->customer,
                'date' => date("d-m-Y", strtotime($out->date)),
            ];
            return $this->sendResponse($convert, 'Create Outgoing successfully.');
        }

    }

    public function updateOutgoing(Request $request, $id)
    {
        $out = outgoing::find($id);

        $checkProduct = Products::where('id', $request->product)->first();
        if (!$checkProduct) {
            return $this->sendError('Product not found', );
        }
        $checkCustomer = Customer::where('id', $request->customer)->first();
        if (!$checkCustomer) {
            return $this->sendError('Customer not found', );
        }

        $validator = Validator::make($request->all(), [
            'product' => 'required',
            "quantity" => "required",
            "customer" => "required",
            "date" => 'nullable|date',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => 'Validation error', 'messages' => $validator->errors()], 400);
        }
        if (!$out) {
            return $this->sendError(error: "Outgoing not found.");
        } else {
            $out->product = $request->product;
            $out->quantity = $request->quantity;
            $out->customer = $request->customer;
            $out->date = $request->date;
        }
        $input = $request->all();
        $input['date'] = $input['date'] ?? Carbon::now()->toDateString();
        $input["product"] = $checkProduct->id;
        $getQty = 0;
        $getQty = $checkProduct->quantity - $request->quantity;
        if ($getQty < 0) {
            return $this->sendError('Quantity is not available', );
        } else {
            $checkProduct->quantity = $getQty;
            $checkProduct->save();
            $out->update($input);
            $convert = [
                'id' => $out->id,
                'product' => $out->product,
                "quantity" => $out->quantity,
                "customer" => $out->customer,
                'date' => date("d-m-Y", strtotime($out->date)),
            ];
            return $this->sendResponse($convert, 'Updated Outgoing successfully.');
        }

    }

    public function getOutgoing($id)
    {
        $out = outgoing::where('id', $id)->first();
        if (!$out) {
            return $this->sendError("Outgoing not found");
        }
        return $this->sendResponse($out, "successfully");
    }

    public function deleteOutgoing(Request $request)
    {
        $out = outgoing::where('id', $request->id)->first();

        if (!$out) {
            return $this->sendError("Outgoing not found.");
        }

        $out->delete();
        return $this->sendResponse($out, "Record $request->id  has been deleted Successfully");

    }

    public function filterByDateOutgoing(Request $request)
    {
        $date = date("d-m-Y", strtotime($request->date));
        $out = outgoing::where('date', 'like', "%$date%")->get();
        if ($out->isEmpty()) {
            return $this->sendError("No outgoing found");
        }

        return $this->sendResponse($out, "Search result");
    }

    public function searchByCustomerOutgoing(Request $request)
    {
        $customer = $request->customer;
        $result = outgoing::where('customer', 'like', "%$customer%")->get();
        if ($result->isEmpty()) {
            return $this->sendError("No outgoing found");
        }

        return $this->sendResponse($result, "Search result");
    }
}
