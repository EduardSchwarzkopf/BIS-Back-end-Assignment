<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;


class ApiAuthController extends Controller
{

    public function login(Request $request)
    {
        $fields = $request->validate([
            'email' => 'required|string',
            'password' => 'required|string'
        ]);

        // Check email
        $user = User::where('email', $fields['email'])->first();

        // Check password
        if (!$user || !Hash::check($fields['password'], $user->password)) {
            return response([
                'message' => 'Wrong password or email'
            ], 401);
        }

        $token = $user->createToken('access_token')->plainTextToken;

        $response = [
            'user' => new UserResource($user),
            'access_token' => $token
        ];

        return response($response, 201);
    }

    public function currentUser()
    {
        $user = auth()->user();

        return new UserResource($user);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        return [
            'message' => 'Logged out'
        ];
    }
}
