<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    /**
     * Register (Signup)
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'nullable|email|unique:users',
            'phone'    => 'nullable|string|unique:users',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->first()
            ], 422);
        }

        // Create user with hashed password
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'phone'    => $request->phone,
            'password' => Hash::make($request->password),
        ]);

        // Generate token
        $token = JWTAuth::fromUser($user);

        return response()->json([
            'token' => $token,
            'user'  => $user
        ]);
    }

    /**
     * Login (Email or Phone + Password)
     */
    public function login(Request $request)
    {
        $request->validate([
            'login'    => 'required|string',   // single field for email or phone
            'password' => 'required|string',
        ]);

        $loginInput = $request->input('login');
        $loginType  = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        $credentials = [
            $loginType => $loginInput,
            'password' => $request->input('password')
        ];

        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Invalid email/phone or password'], 401);
            }
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(['error' => 'Could not create token'], 500);
        }

        return response()->json([
            'token' => $token,
            'user'  => JWTAuth::user()
        ]);
    }

    /**
     * Get Authenticated User
     */
    public function me()
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            return response()->json($user);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(['error' => 'Token invalid or expired'], 401);
        }
    }

    /**
     * Logout
     */
    public function logout(Request $request)
{
    try {
        $token = JWTAuth::getToken();

        if (! $token) {
            return response()->json(['error' => 'Token not provided'], 400);
        }

        JWTAuth::invalidate($token);

        return response()->json(['message' => 'Logged out successfully']);
    } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        return response()->json(['error' => 'Token has already expired'], 401);
    } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
        return response()->json(['error' => 'Token is invalid'], 401);
    } catch (\Tymon\JWTAuth\Exceptions\TokenBlacklistedException $e) {
        return response()->json(['error' => 'Token has been blacklisted'], 401);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Could not logout'], 500);
    }
}

}
