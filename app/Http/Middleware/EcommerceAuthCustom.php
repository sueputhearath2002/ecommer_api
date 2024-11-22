<?php

namespace App\Http\Middleware;

use App\Http\Controllers\API\BaseController;
use App\Models\TokenApi;
use App\Models\User;
use Closure;
use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

class EcommerceAuthCustom extends BaseController
{
    public function handle(Request $request, Closure $next, ...$guards)
    {

        $myToken = $request->token;
        $token = TokenApi::where("token", $myToken)->first();

        if (!$token || empty($token->token)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authenticated or the token is invalid.'
            ], 401); // Return Unauthorized error
        }

        $user = User::where("id", $token->user_id)->first();
        Auth::login($user);
        return $next($request);

    }
}
