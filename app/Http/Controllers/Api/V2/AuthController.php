<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends BaseController
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:15',
            'password' => 'required|string|min:8|confirmed',
            'honeypot' => 'nullable|max:0',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        if ($request->filled('honeypot')) {
            return $this->errorResponse('Invalid submission detected', 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('mobile-app')->plainTextToken;

        return $this->successResponse([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
            ],
            'token' => $token,
        ], 'User registered successfully', 201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
            'honeypot' => 'nullable|max:0',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        if ($request->filled('honeypot')) {
            return $this->errorResponse('Invalid submission detected', 422);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            return $this->unauthorizedResponse('Invalid credentials');
        }

        $user = Auth::user();

        $user->tokens()->where('name', 'mobile-app')->delete();

        $token = $user->createToken('mobile-app')->plainTextToken;

        return $this->successResponse([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
            ],
            'token' => $token,
        ], 'Login successful');
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->successResponse(null, 'Logged out successfully');
    }

    public function refresh(Request $request)
    {
        $user = $request->user();

        $request->user()->currentAccessToken()->delete();

        $token = $user->createToken('mobile-app')->plainTextToken;

        return $this->successResponse([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
            ],
            'token' => $token,
        ], 'Token refreshed successfully');
    }

    public function deleteAccount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $user = $request->user();

        if (!Hash::check($request->password, $user->password)) {
            return $this->unauthorizedResponse('Invalid password');
        }

        $user->tokens()->delete();

        $user->delete();

        return $this->successResponse(null, 'Account deleted successfully');
    }
}
