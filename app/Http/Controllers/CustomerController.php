<?php

namespace App\Http\Controllers;

use App\Http\Controllers\API\BaseController;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CustomerController extends BaseController
{
    public function index(): JsonResponse
    {
        $customer = Customer::paginate("5");
        $data = [
            'customers' => $customer->items(),
            'per_page' => $customer->perPage(),
            'current_page' => $customer->currentPage(),
            'last_pages' => $customer->lastPage(),
        ];
        return $this->sendResponse($data, 'Customer retrieved successfully.');
    }

    public function storeCustomer(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            "gender" => "required",
            "address" => "required",
            'phone_number' => 'required',
            'date_of_birth' => 'required',

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
        $customer = Customer::create($input);
        $convert = [
            'id' => $customer->id,
            'name' => $customer->name,
            "phone_number" => $customer->phone_number,
            "address" => $customer->address,
            "gender" => $customer->gender,
            'date_of_birth' => $customer->date_of_birth,
            'imgUrl' => $customer->imgUrl,
        ];
        return $this->sendResponse($convert, 'Create Products successfully.');
    }

    public function updateCustomer(Request $request, $id)
    {
        $customer = Customer::find($id);
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            "gender" => "required",
            "address" => "required",
            'phone_number' => 'required',
            'date_of_birth' => 'required',

        ]);
        if ($validator->fails()) {
            return response()->json(['error' => 'Validation error', 'messages' => $validator->errors()], 400);
        }
        if (!$customer) {
            return $this->sendError(error: "Customer not found.");
        } else {
            $customer->name = $request->name;
            $customer->gender = $request->gender;
            $customer->address = $request->address;
            $customer->phone_number = $request->phone_number;
            $customer->date_of_birth = $request->date_of_birth;
            $customer->imgUrl = $request->imgUrl;
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
        $customer->update($input);
        $convert = [
            'id' => $customer->id,
            'name' => $customer->name,
            "gender" => $customer->gender,
            "address" => $customer->address,
            'phone_number' => $customer->phone_number,
            'date_of_birth' => $customer->date_of_birth,
            'imgUrl' => $customer->imgUrl ?? "",
        ];
        return $this->sendResponse($convert, 'Updated Customer successfully.');

    }

    public function deleteCustomer(Request $request)
    {
        $customer = Customer::where('id', $request->id)->first();

        if (!$customer) {
            return $this->sendError("Customer not found.");
        }

        $customer->delete();
        return $this->sendResponse($customer, "Record $request->id  has been deleted Successfully");

    }
}
