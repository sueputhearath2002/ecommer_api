<?php

namespace App\Http\Controllers;

use App\Http\Controllers\API\BaseController;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
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
        // Validate input fields
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'gender' => 'required',
            'address' => 'required',
            'phone_number' => 'required',
            'date_of_birth' => 'required',
        ]);

        // If validation fails, return error response
        if ($validator->fails()) {
            return response()->json(['error' => 'Validation error', 'messages' => $validator->errors()], 400);
        }

        // Prepare input data
        $input = $request->all();

        // Handle image upload if file is present
        if ($request->hasFile('imgUrl')) {
            $file = $request->file('imgUrl');
            $extension = $file->getClientOriginalExtension();

            // Validate file type and size
            if (in_array($extension, ['jpeg', 'png', 'jpg', 'gif', 'svg'])) {
                if ($file->getSize() <= 2 * 1024 * 1024) { // Max size 2MB
                    $filename = time() . '.' . $extension;
                    $file->move(public_path('images/products'), $filename);

                    // Store the uploaded image URL
                    $input['imgUrl'] = url('images/products/' . $filename);

                    // Prepare the image file for the API request
                    $filePath = public_path('images/products/' . $filename);
                    // Send the image to the PhotoRoom API
                    $response = Http::withHeaders([
                        'X-Api-Key' => config('app.remove_bg.api_key'), // Example API key for remove.bg
                    ])
                        ->timeout(60)
                        ->attach(
                            'image_file',
                            file_get_contents($filePath),
                            $filename
                        )
                        ->post('https://api.remove.bg/v1.0/removebg');

                    // Check if the response status code is 200 (successful)
                    if ($response->getStatusCode() != 200) {
                        return response()->json(['error' => 'Error from PhotoRoom API', 'message' => $response->body()], 400);
                    }

                    // Save the processed image (response from PhotoRoom API)
                    $processedImage = $response->body();
                    $processedImagePath = public_path('images/products/processed_' . $filename);
                    file_put_contents($processedImagePath, $processedImage);

                    // Update the image URL for the processed image
                    $input['imgUrl'] = url('images/products/processed_' . $filename);
                } else {
                    return response()->json(['error' => 'File is too large. Maximum size is 2MB.'], 400);
                }
            } else {
                return response()->json(['error' => 'Invalid image type. Allowed types: jpeg, png, jpg, gif, svg.'], 400);
            }
        } else {
            // No image uploaded, set default image URL as empty
            $input['imgUrl'] = '';
        }

        // Create the customer
        $customer = Customer::create($input);

        // Prepare the response
        $response = [
            'id' => $customer->id,
            'name' => $customer->name,
            'phone_number' => $customer->phone_number,
            'address' => $customer->address,
            'gender' => $customer->gender,
            'date_of_birth' => $customer->date_of_birth,
            'imgUrl' => $customer->imgUrl,
        ];

        // Return success response
        return response()->json(['data' => $response, 'message' => 'Customer created successfully.'], 200);
    }
    public function getCustomer($id)
    {
        $customer = Customer::where('id', $id)->first();
        if (!$customer) {
            return $this->sendError("Customer not found");
        }

        // If the product is found, return it
        return $this->sendResponse($customer, "successfully");
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
