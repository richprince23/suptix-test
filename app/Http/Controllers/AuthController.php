<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Login form to authenticate user via api
     * Auth tokens are generated and sent alongside user details
     *
     * @return JsonResponse
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'The provided credentials do not match our records.',
                'status' => 401,
                'user' => null,
                'token' => null,
            ], 401);
        }

        $user->load(['organizer']);
        // generate token
        $token = $user->createToken("{$user->name}'s Device")->plainTextToken;

        return response()->json(['message' => 'Signed in successfully', 'status' => 200, 'user' => $user, 'token' => $token], 200);

    }

    /**
     * Logs out user and deletes auth tokens
     */
    public function logout(Request $request): JsonResponse
    {
        if (! $request->user()) {
            return response()->json(['message' => 'You are not authenticated', 'status' => 401]);
        }

        // Revoke the token that was used to authenticate the current request...
        // $request->session()->invalidate();
        // $request->session()->regenerateToken();
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully', 'status' => 200]);

    }


    public function logoutUser(Request $request): JsonResponse
    {
        if (! $request->user()) {
            return response()->json(['message' => 'You are not authenticated', 'status' => 401]);
        }

        // Revoke the token that was used to authenticate the current request...
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully', 'status' => 200]);

    }



    /**
     * Delete user account only (keep related data intact)
     */
    public function deleteAccount(Request $request): JsonResponse
    {
        $request->validate([
            'password' => 'required|string',
            'confirmation' => 'required|string|in:DELETE_MY_ACCOUNT',
        ]);

        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authenticated',
            ], 401);
        }

        // Verify password
        if (! Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid password provided',
            ], 400);
        }

        try {
            // Start a database transaction
            DB::beginTransaction();

            // Remove user from all organizers (many-to-many relationships)
            $user->organizers()->detach();

            // Delete all user's personal access tokens
            $user->tokens()->delete();

            // Delete the user account only - leave all other data intact
            $user->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Your account has been successfully deleted. Your data has been preserved.',
            ], 200);

        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting your account. Please try again or contact support.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get account deletion information (what will be deleted)
     */
    public function getAccountDeletionInfo(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authenticated',
            ], 401);
        }

        // Get user's organizers
        $organizers = $user->organizers()->get();

        $ordersCount = DB::table('orders')
            ->where('buyer_email', $user->email)
            ->count();

        $ticketsCount = DB::table('tickets')
            ->where('attendee_email', $user->email)
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'created_at' => $user->created_at,
                ],
                'organizers' => $organizers->map(function ($org) {
                    return [
                        'id' => $org->id,
                        'name' => $org->name,
                        'role' => 'member',
                    ];
                }),
                'personal_data' => [
                    'orders_count' => $ordersCount,
                    'tickets_count' => $ticketsCount,
                ],
                'can_delete' => true, // Always true since we only delete user account
                'deletion_info' => [
                    'what_will_be_deleted' => [
                        'Your user account and profile',
                        'All authentication tokens',
                        'Your membership in organizations',
                    ],
                    'what_will_be_kept' => [
                        'All your order records (linked by email)',
                        'All your ticket information (linked by email)',
                        'All event and organization data',
                        'Your purchase history and transaction records',
                    ],
                ],
            ],
        ]);
    }
}
