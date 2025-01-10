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
     */
    public function index()
    {
        $Profiles = Profile::paginate();
        return $this->paginationResponse($Profiles);
    }

    /**
     * Retrieve a specific profile by ID if it belongs to the authenticated user.
     */
    public function show($id)
    {
        $profile = Profile::find($id);

        if (!$profile) {
            return $this->errorResponse('Profile not found', 404);
        }

         return $this->dataResponse($profile, "Profile details retrieved successfully");
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
            'photo_path' => 'nullable|string|max:255',
            'child_profile' => 'nullable|boolean',
            'date_of_birth' => 'required|date|before:today',
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
                'photo_path' => $validated['photo_path'],
                'child_profile' => $validated['child_profile'],
                'date_of_birth' => $validated['date_of_birth'],
                'language' => $validated['language'],
            ]);

            // Commit the transaction after successful user creation
            DB::commit();

            // Return the response with subscription data
            return $this->dataResponse([
                'profile' => $profile->only(['profile_id', 'user_id', 'name', 'photo_path', 'child_profile', 'date_of_birth', 'language']),
            ], "Subscription created successfully.");
        } catch (\Exception $e) {
            // If anything goes wrong, roll back the transaction
            DB::rollBack();

            Log::error($e);
            // Return error response in case of failure
            return $this->errorResponse(500, 'Failed to add new profile. Please try again later.');

        }
    }
    /**
     * Update a specific profile by ID if it belongs to the authenticated user.
     */
    public function update(Request $request, $id)
    {

        $profile = Profile::find($id);
        if (!$profile) {
            return $this->errorResponse('Profile not found', 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'photo_path' => 'sometimes|string|max:255',
            'date_of_birth' => 'sometimes|date|before:today',
            'child_profile' => 'sometimes|boolean',
            'language' => 'sometimes|string|max:10',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(400, $validator->errors()->first());
        }

        $validated = $validator->safe();

        // Start database transaction
        DB::beginTransaction();
        try {
            // Update the profile record in the database
            $profile->update($validated->toArray());

            // Commit the transaction after successful update
            DB::commit();

            // Return the response with subscription data
            return $this->dataResponse([
                'profile' => $profile->only(['profile_id','user_id', 'name', 'photo_path', 'child_profile', 'date_of_birth', 'language']),
            ], "Profile updated successfully.");
        } catch (\Exception $e) {
            // If anything goes wrong, roll back the transaction
            DB::rollBack();

            Log::error($e);
            // Return error response in case of failure
            return $this->errorResponse(500, 'Failed to update profile. Please try again later.');

            }
    }

    /**
     * Delete a specific profile by ID if it belongs to the authenticated user.
     */
    public function destroy($id)
    {
        $profile = Profile::find($id);
        if (!$profile) {
            return $this->errorResponse('Profile not found', 404);
        }
        $profile->delete();
        return $this->messageResponse('Profile deleted successfully.', 200);
    }
}
