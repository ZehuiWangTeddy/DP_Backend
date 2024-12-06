<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Get the current authenticated user's information.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserInfo()
    {
        $user = auth()->user(); // Get authenticated user

        return response()->json([
            'meta' => [
                'code' => 200,
                'status' => 'success',
                'message' => 'User info fetched successfully',
            ],
            'data' => [
                'user' => $user->only(['id', 'name', 'email', 'address', 'user_role']),
            ],
        ]);
    }

    /**
     * Update the authenticated user's information.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateUserInfo(Request $request)
    {
        $user = auth()->user(); // Get authenticated user

        // Validate the input data
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:100',
            'email' => 'sometimes|email|max:100|unique:users,email,' . $user->id,
            'address' => 'sometimes|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'meta' => [
                    'code' => 400,
                    'status' => 'error',
                    'message' => $validator->errors()->first(),
                ],
            ], 400);
        }

        // Update the user's information
        $user->update($validator->validated());

        return response()->json([
            'meta' => [
                'code' => 200,
                'status' => 'success',
                'message' => 'User info updated successfully',
            ],
            'data' => [
                'user' => $user->only(['id', 'name', 'email', 'address', 'user_role']),
            ],
        ]);
    }

    /**
     * Get a list of all users (only for admin users with user_role 0).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllUsers()
    {
        $user = auth()->user();

        // Check if the user is an admin (user_role 0)
        if ($user->user_role !== 0) {
            return response()->json([
                'meta' => [
                    'code' => 403,
                    'status' => 'error',
                    'message' => 'Forbidden: You do not have permission to access this resource.',
                ],
            ], 403);
        }

        // Fetch all users (excluding sensitive data)
        $users = User::all(['id', 'name', 'email', 'address', 'user_role']);

        return response()->json([
            'meta' => [
                'code' => 200,
                'status' => 'success',
                'message' => 'All users fetched successfully',
            ],
            'data' => [
                'users' => $users,
            ],
        ]);
    }

    /**
     * Get a specific user's information by ID.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSpecificUserInfo($id)
    {
        $user = auth()->user(); // Get authenticated user

        // Fetch the specific user by ID
        $targetUser = User::find($id);

        if (!$targetUser) {
            return response()->json([
                'meta' => [
                    'code' => 404,
                    'status' => 'error',
                    'message' => 'User not found.',
                ],
            ], 404);
        }

        // If the user_role is 0 (admin), they can view other users' information
        if ($user->user_role === 0 || $user->id === $id) {
            return response()->json([
                'meta' => [
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'User info fetched successfully',
                ],
                'data' => [
                    'user' => $targetUser->only(['id', 'name', 'email', 'address', 'user_role']),
                ],
            ]);
        }

        return response()->json([
            'meta' => [
                'code' => 403,
                'status' => 'error',
                'message' => 'Forbidden: You do not have permission to access this resource.',
            ],
        ], 403);
    }

    /**
     * Update a specific user's information by ID (only for admins).
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateSpecificUserInfo(Request $request, $id)
    {
        $user = auth()->user(); // Get authenticated user

        // Check if the authenticated user is an admin
        if ($user->user_role !== 0) {
            return response()->json([
                'meta' => [
                    'code' => 403,
                    'status' => 'error',
                    'message' => 'Forbidden: You do not have permission to perform this action.',
                ],
            ], 403);
        }

        // Validate the input data
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:100',
            'email' => 'sometimes|email|max:100|unique:users,email,' . $id,
            'address' => 'sometimes|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'meta' => [
                    'code' => 400,
                    'status' => 'error',
                    'message' => $validator->errors()->first(),
                ],
            ], 400);
        }

        // Find the specific user
        $targetUser = User::find($id);

        if (!$targetUser) {
            return response()->json([
                'meta' => [
                    'code' => 404,
                    'status' => 'error',
                    'message' => 'User not found.',
                ],
            ], 404);
        }

        // Update the specific user's information
        $targetUser->update($validator->validated());

        return response()->json([
            'meta' => [
                'code' => 200,
                'status' => 'success',
                'message' => 'User info updated successfully',
            ],
            'data' => [
                'user' => $targetUser->only(['id', 'name', 'email', 'address', 'user_role']),
            ],
        ]);
    }

    /**
     * Delete a specific user (only for admins).
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteSpecificUser($id)
    {
        $user = auth()->user(); // Get authenticated user

        // Check if the authenticated user is an admin
        if ($user->user_role !== 0) {
            return response()->json([
                'meta' => [
                    'code' => 403,
                    'status' => 'error',
                    'message' => 'Forbidden: You do not have permission to perform this action.',
                ],
            ], 403);
        }

        // Find the specific user
        $targetUser = User::find($id);

        if (!$targetUser) {
            return response()->json([
                'meta' => [
                    'code' => 404,
                    'status' => 'error',
                    'message' => 'User not found.',
                ],
            ], 404);
        }

        // Delete the user
        $targetUser->delete();

        return response()->json([
            'meta' => [
                'code' => 200,
                'status' => 'success',
                'message' => 'User deleted successfully',
            ],
        ]);
    }
}

