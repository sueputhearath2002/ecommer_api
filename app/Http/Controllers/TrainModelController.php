<?php

namespace App\Http\Controllers;

use App\Http\Controllers\API\BaseController;
use App\Models\TrainModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TrainModelController extends BaseController
{
    public function storeTrain(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required',
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
        $supply = TrainModel::create($input);
        $convert = [
            'id' => $supply->id,
            'name' => $supply->name,
            'imgUrl' => $supply->imgUrl,
        ];
        return $this->sendResponse($convert, 'Create Train successfully.');
    }
}
