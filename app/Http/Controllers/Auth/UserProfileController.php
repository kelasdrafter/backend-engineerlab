<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserProfileController extends Controller
{
    /**
     * Update the currently authenticated user.
     */
    public function update(UpdateUserRequest $request): JsonResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
            $request->user()->sendEmailVerificationNotification();
        }

        $request->user()->save();

        return $this->responseSuccess('Create Data Succcessfully', new UserResource(Auth::user()->fresh()), 200);
    }

    /**
     * Update the password of the currently authenticated user.
     */
    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'password' => ['required', 'confirmed', Rules\Password::defaults()]
        ]);

        $request->user()->update([
            'password' => Hash::make($request->password)
        ]);

        return $this->responseSuccess('Create Data Succcessfully', new UserResource($request->user()), 200);
    }


    /**
     * Show the currently authenticated user.
     */
    public function show(): JsonResponse
    {
        return $this->responseSuccess('Create Data Succcessfully', new UserResource(Auth::user()), 200);
    }
}
