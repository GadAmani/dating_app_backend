<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Register (Signup)
     */
    public function register(Request $request)
    {
        $validator = $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'nullable|email|unique:users',
            'phone'         => 'nullable|string|unique:users',
            'password'      => 'required|string|min:6|confirmed',
            'gender'        => 'required|in:male,female',
            'description'   => 'required|string|max:1000',
            'profile_image' => 'required|image|max:2048',
            'age'           => 'required|integer|min:18|max:120',
        ]);

        $validator['password'] = Hash::make($validator['password']);

        if ($request->hasFile('profile_image')) {
            $path = $request->file('profile_image')->store('profile_images', 'public');
            $validator['profile_image'] = $path;
        }

        try {
            $user = User::create($validator);

            // Return full URL for profile image
            $user->profile_image = $user->profile_image ? asset('storage/' . $user->profile_image) : null;

            return response()->json([
                'message' => 'Registration Successful!',
                'user'    => $user,
            ], 201);
        } catch (\Exception $exception) {
            return response()->json([
                'error'   => 'Registration failed',
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
            'login'    => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $validated['login'])
                    ->orWhere('phone', $validated['login'])
                    ->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'error'   => 'Login failed',
                'message' => 'Invalid credentials'
            ], 401);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        // Full URL for profile image
        $user->profile_image = $user->profile_image ? asset('storage/' . $user->profile_image) : null;

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
        return response()->json(['message' => 'Log Out Successful.']);
    }

    /**
     * Get opposite-gender users
     */
    public function getOppositeGenderUsers(Request $request)
    {
        $user = $request->user();
        $users = User::where('gender', $user->gender === 'male' ? 'female' : 'male')->get();

        $users = $users->map(function ($u) {
            $u->profile_image = $u->profile_image ? asset('storage/' . $u->profile_image) : null;
            return $u;
        });

        return response()->json($users);
    }

    /**
     * Get liked/matched profiles (example)
     */
    public function getMatchedProfiles(Request $request)
    {
        $user = $request->user();

        // Example: assuming you have a many-to-many relationship 'likes'
        $matches = $user->likedUsers()->get(); // Replace with your relationship

        $matches = $matches->map(function ($u) {
            $u->profile_image = $u->profile_image ? asset('storage/' . $u->profile_image) : null;
            return $u;
        });

        return response()->json($matches);
    }
}

