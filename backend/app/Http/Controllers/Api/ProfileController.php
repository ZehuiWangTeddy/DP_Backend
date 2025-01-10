<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\Profile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends BaseController
{
    /**
     * Retrieve all profiles associated with the authenticated user.
     *
     * @return JsonResponse
     */
    public function index()
    {
        $Profiles = Profile::paginate();
        return $this->paginationResponse($Profiles);
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
     *
     * @param Request $request
     * @param int $user_id
     * @return JsonResponse
     */
    public function store(Request $request, $user_id): JsonResponse
    {
        // Validate the request data
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'avatar' => 'nullable|string|max:255',
            'is_kids' => 'nullable|boolean',
            'language' => 'required|string|max:10',
        ]);

        // Create a new profile
        $profile = Profile::create([
            'user_id' => $user_id,
            'name' => $validated['name'],
            'avatar' => $validated['avatar'] ?? null,
            'is_kids' => $validated['is_kids'] ?? false,
            'language' => $validated['language'],
        ]);

        return response()->json(
            [
                'data' => $profile,
                'message' => 'Profile created successfully'
            ],
            201
        );
    }
}
