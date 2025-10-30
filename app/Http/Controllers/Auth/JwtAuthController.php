<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class JwtAuthController extends Controller
{
    /**
     * Register a new user and return an access token.
     */
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'phone' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'birthdate' => ['nullable', 'date'],
            'role' => ['nullable', 'string', 'max:255'],
            'photo_url' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'city' => $request->city,
            'birthdate' => $request->birthdate,
            'role' => $request->input('role', 'user'),
            'photo_url' => $request->photo_url,
            'is_active' => $request->input('is_active', true),
        ]);

        event(new Registered($user));

        return response()->json([
            'status' => 'user-created'
        ]);
    }

    /**
     * Authenticate a user and return an access token.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $credentials = $request->only(['email', 'password']);

        $token = Auth::attempt($credentials);

        if (!$token) {
            return response()->json([
                'status' => 'wrong-credentials'
            ], 401);
        }

        return response()->json([
            'user' => new UserResource(Auth::user()),
            'access_token' => $token,
        ]);
    }

    /**
     * Log out the currently authenticated user (invalidate the token).
     */
    public function logout(): Response
    {
        Auth::logout();

        return response()->noContent();
    }

    /**
     * Refresh the currently authenticated user's access token.
     */
    public function refresh(): JsonResponse
    {
        $token = Auth::refresh();

        return response()->json([
            'access_token' => $token,
        ]);
    }
}
