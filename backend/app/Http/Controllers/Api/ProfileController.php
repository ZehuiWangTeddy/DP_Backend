<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\Profile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProfileController extends BaseController
{
    /**
     * Retrieve all profiles associated with the authenticated user.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $profiles = Profile::where('user_id', Auth::id())->paginate();
        return response()->json([
            'data' => $profiles,
            'message' => 'Profiles retrieved successfully'
        ]);
    }

    /**
     * Retrieve a specific profile by ID if it belongs to the authenticated user.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        $profile = Profile::where('user_id', Auth::id())
            ->where('profile_id', $id)
            ->first();

        if (!$profile) {
            return response()->json(
                [
                    'data' => null,
                    'message' => 'Profile not found'
                ],
                404
            );
        }

        return response()->json(
            [
                'data' => $profile,
                'message' => 'Profile retrieved successfully'
            ],
            200
        );
    }

    /**
     * Update a specific profile by ID if it belongs to the authenticated user.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        $profile = Profile::where('user_id', Auth::id())
            ->where('profile_id', $id)
            ->first();

        if (!$profile) {
            return response()->json(
                [
                    'data' => null,
                    'message' => 'Profile not found'
                ],
                404
            );
        }

        // Validate the request data
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'avatar' => 'sometimes|string|max:255',
            'is_kids' => 'sometimes|boolean',
        ]);

        // Update the profile with the validated data
        $profile->update($validated);

        return response()->json(
            [
                'data' => $profile,
                'message' => 'Profile updated successfully'
            ],
            200
        );
    }

    /**
     * Delete a specific profile by ID if it belongs to the authenticated user.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        $profile = Profile::where('user_id', Auth::id())
            ->where('profile_id', $id)
            ->first();

        if (!$profile) {
            return response()->json(
                [
                    'data' => null,
                    'message' => 'Profile not found'
                ],
                404
            );
        }

        // Delete the profile
        $profile->delete();

        return response()->json(
            [
                'data' => null,
                'message' => 'Profile deleted successfully'
            ],
            200
        );
    }

    /**
     * Store a new profile for the authenticated user.
     */
    public function store(Request $request)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users',
            'name' => 'required|string|max:255',
            'avatar' => 'nullable|string|max:255',
            'is_kids' => 'nullable|boolean',
            'language' => 'required|string|max:10',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(400, $validator->errors()->first());
        }

        $validated = $validator->safe();

        // Start database transaction
        DB::beginTransaction();

        try {
            // Create the new profile record in the database
            $profile = Profile::create([
                'user_id' => $validated['user_id'],
                'name' => $validated['name'],
                'avatar' => $validated['avatar'],
                'is_kids' => $validated['is_kids'],
                'language' => $validated['language'],
            ]);

            // Commit the transaction after successful user creation
            DB::commit();

            // Return the response with subscription data
            return $this->dataResponse([
                'profile' => $profile->only(['profile_id', 'user_id', 'name', 'avatar', 'is_kids', 'language']),
            ], "Subscription created successfully.");
        } catch (\Exception $e) {
            // If anything goes wrong, roll back the transaction
            DB::rollBack();

            Log::error($e);
            // Return error response in case of failure
            return $this->errorResponse(500, 'Failed to add new profile. Please try again later.');

        }
    }
}
