<?php

namespace App\Http\Controllers;

use App\Http\Controllers\API\BaseController;
use App\Models\Categories;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoriesController extends BaseController
{
    public function index(): JsonResponse
    {
        $categories = Categories::all();
        return $this->sendResponse($categories, 'Categories retrieved successfully.');
    }

    public function storeCategories(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'code' => 'required',
            'detail' => '',
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

        }
        $categories = Categories::create($input);
        $convert = [
            'id' => $categories->id,
            'name' => $categories->name,
            'code' => $categories->code,
            'detail' => $categories->detail,
            'imgUrl' => $categories->imgUrl ?? "",
        ];
        return $this->sendResponse($convert, 'Create Categories successfully.');
    }

    public function getCategory($id)
    {
        $product = Categories::where('id', $id)->first();
        if (!$product) {
            return $this->sendError("Category not found");
        }
        return $this->sendResponse($product, "successfully");
    }

    public function updateCategory(Request $request, $id)
    {
        $category = Categories::find($id);
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            "code" => "required",
            "imgUrl" => "",
            'detail' => '',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => 'Validation error', 'messages' => $validator->errors()], 400);
        }
        if (!$category) {
            return $this->sendError("Category not found.");
        } else {
            $category->name = $request->name;
            $category->code = $request->code;
            $category->detail = $request->detail;

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

        }
        $category->update($input);
        $convert = [
            'id' => $category->id,
            'name' => $category->name,
            "code" => $category->code,
            'detail' => $category->detail,
            'imgUrl' => $category->imgUrl ?? "",
        ];
        return $this->sendResponse($convert, 'Updated Category successfully.');

    }

    public function deleteCategory(Request $request)
    {
        $category = Categories::where('id', $request->id)->first();

        if (!$category) {
            return $this->sendError("Category not found.");
        }

        $category->delete();
        return $this->sendResponse($category, "Record $request->id  has been deleted Successfully");

    }

}
