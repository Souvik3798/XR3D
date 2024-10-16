<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserController extends Controller
{
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8',
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            // Check if the user model has the createToken method
            if (method_exists($user, 'createToken')) {
                $token = $user->createToken('auth_token')->plainTextToken;
                return response()->json([
                    'user' => $user,
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                ], 201);
            } else {
                // If createToken method doesn't exist, return user without token
                return response()->json([
                    'user' => $user,
                    'message' => 'User registered successfully, but token creation is not available.',
                ], 201);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while registering the user', 'error' => $e->getMessage()], 500);
        }
    }

    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|string|email',
                'password' => 'required|string',
            ]);

            if (!Auth::attempt($request->only('email', 'password'))) {
                return response()->json([
                    'message' => 'Invalid login details'
                ], 401);
            }

            $user = User::where('email', $request->email)->firstOrFail();

            // Check if the user model has the createToken method
            if (method_exists($user, 'createToken')) {
                $token = $user->createToken('auth_token')->plainTextToken;
                return response()->json([
                    'user' => $user,
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                ]);
            } else {
                // If createToken method doesn't exist, return user without token
                return response()->json([
                    'user' => $user,
                    'message' => 'Login successful, but token creation is not available.',
                ]);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while logging in', 'error' => $e->getMessage()], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'message' => 'Successfully logged out'
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while logging out', 'error' => $e->getMessage()], 500);
        }
    }
}
