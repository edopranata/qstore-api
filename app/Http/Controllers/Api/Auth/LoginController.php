<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    /**
     * Handle the incoming request.
     */

    public function __invoke(LoginRequest $request)
    {
        $user = User::where('username', $request->username)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'errors' => [
                    'password' => ['The provided credentials are incorrect.']
                ],
            ], 422);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'type' => 'Bearer',
            'permissions' => $user->getAllPermissions()->pluck('name')
        ], 201);
    }
}
