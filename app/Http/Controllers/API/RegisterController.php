<?php

namespace App\Http\Controllers\API;

use App\Models\TokenApi;
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;

class RegisterController extends BaseController
{
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            'c_password' => 'required|same:password',
        ]);



        if ($validator->fails()) {
            return response()->json(['error' => 'Validation error', 'messages' => $validator->errors()], 400);

        }
        $user = User::where('email', $request->email)->first();

        if ($user) {
            return $this->sendError("Email is Existed!", "", 400);
        }


        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user = User::create(attributes: $input);
        $success['token'] = $user->createToken('MyApp')->plainTextToken;
        $success['name'] = $user->name;

        return $this->sendResponse($success, 'User register successfully.');
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Validation error', 'messages' => $validator->errors()], 400);
        }

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {

            $user = Auth::user();
            $token = $user->createToken('MyApp')->plainTextToken;
            TokenApi::where("user_id", operator: $user->id)->delete();
            $tokenApi = new TokenApi();
            $tokenApi->token = $token;
            $tokenApi->user_id = $user->id;
            $tokenApi->save();


            return response()->json([
                'success' => true,
                'token' => $token,
                'name' => $user->name,
            ]);
        }

        return response()->json(['error' => 'Unauthorized', 'message' => 'Invalid credentials'], 401);
    }

    public function getInfoUser()
    {
        $user = Auth::user();
        if (!$user) {
            return $this->sendError("Unauthorized", "", 401);
        }
        return $this->sendResponse($user, "success");
    }

    public function logout(Request $request)
    {
        $userToken = TokenApi::where("token", $request->token);
        $userToken->delete();
        return $this->sendResponse($userToken, "Logout successfully");
    }
}
