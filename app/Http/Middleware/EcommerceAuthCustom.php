<?php

namespace App\Http\Middleware;

use App\Models\TokenApi;
use Closure;
use Illuminate\Http\Request;

class EcommerceAuthCustom
{
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $myToken = $request->token;
        $token = TokenApi::where("token", $myToken)->first();
        // dd($token);
        // Example token (this could be dynamic)

        // dd($token,$myToken);

        if (!$token || empty($token->token)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authenticated or the token is invalid.'
            ], 401); // Return Unauthorized error
        }

        return $next($request);  // Proceed to the next middleware or controller
    }
}
