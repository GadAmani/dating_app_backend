<?php

namespace App\Http\Controllers;

use App\Models\role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register (Signup)
     */
    public function register(Request $request)
    {
        $validator = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'nullable|email|unique:users',
            'phone'    => 'nullable|string|unique:users',
            'password' => 'required|string|min:6',
        ]);

         $validator['password'] = Hash::make($validator['password']);

        try {
            $user = User::create($validator);

            return response()->json([
                'message' => 'Registration Successful!',
                'user' => $user,
            ], 201);
        } catch (\Exception $exception) {
            return response()->json([
                'error' => 'Registration failed',
                'message' => $exception->getMessage()
            ], 500);
        }
    }

    /**
     * Login (Email or Phone + Password)
     */
    public function login(Request $request)
{
    $validated = $request->validate([
        'login'    => 'required|string',   // can be email or phone
        'password' => 'required|string',
    ]);

    // Find user by email or phone
    $user = User::where('email', $validated['login'])
                ->orWhere('phone', $validated['login'])
                ->first();

    if (!$user || !Hash::check($validated['password'], $user->password)) {
        return response()->json([
            'error' => 'Login failed',
            'message' => 'Invalid credentials'
        ], 401);
    }

    // Sanctum token
    $token = $user->createToken('auth-token')->plainTextToken;

    return response()->json([
        'message' => 'Login Successful!',
        'user'    => $user,
        'token'   => $token
    ], 200);
}


    /**
     * Logout
     */
   public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'message' => 'Log Out Successful.'
        ]);
    }

}
