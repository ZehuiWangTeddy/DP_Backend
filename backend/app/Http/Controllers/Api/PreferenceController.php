<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\Preference;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;


class PreferenceController extends BaseController
{
    public function show($profileId)
    {
        $preference = Preference::where('profile_id', $profileId)->first();

        if (!$preference) {
            return $this->errorResponse('Preference not found', 404);
        }

        return $this->dataResponse($preference, 'Preferences retrieved successfully');
    }

    public function store(Request $request, $profileId)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'content_type' => [
                'required',
                'string',
                'max:255',
                Rule::in([
                    '18+', 'For Kids', 'Includes Violence', 'Includes Sex',
                    'Family Friendly', 'Educational', 'Sci-Fi Themes', 'Fantasy Elements',
                ]),
            ],
            'content_preference' => [
                'nullable',
                'string',
                'max:255',
                Rule::in(['movies', 'series', 'both']),
            ],
            'genre' => [
                'required',
                'array',
                function ($attribute, $value, $fail) {
                    $allowedGenres = [
                        'Action', 'Comedy', 'Drama', 'Horror', 'Thriller',
                        'Fantasy', 'Science Fiction', 'Romance', 'Documentary',
                        'Animation', 'Crime', 'Mystery', 'Adventure', 'Western', 'Biographical',
                    ];
                    foreach ($value as $genre) {
                        if (!in_array($genre, $allowedGenres)) {
                            return $fail("The $attribute contains an invalid genre: $genre.");
                        }
                    }
                },
            ],
            'genre.*' => 'string|max:255',
            'minimum_age' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(400, $validator->errors()->first());
        }

        $validated = $validator->safe();

        // Check if the profile exists
        $profile = Profile::find($profileId);
        if (!$profile) {
            return $this->errorResponse(404, 'Profile not found.');
        }

        // Check if the profile already has a preference
        $existingPreference = Preference::where('profile_id', $profileId)->exists();
        if ($existingPreference) {
            return $this->errorResponse(400, 'This profile already has a preference. Please update it instead.');
        }

        // Convert genre to JSON format
        $validated['genre'] = json_encode($validated['genre']);

        // Start database transaction
        DB::beginTransaction();

        try {
            // Create the preference record in the database
            $preference = Preference::create([
                'profile_id' => $profileId,
                'content_type' => $validated['content_type'],
                'content_preference' => $validated['content_preference'],
                'genre' => $validated['genre'],
                'minimum_age' => $validated['minimum_age'],
            ]);

            // Commit the transaction
            DB::commit();

            // Return success response
            return $this->dataResponse([
                'preference' => $preference->only(['preference_id', 'profile_id', 'content_type', 'content_preference', 'genre', 'minimum_age']),
            ], "Preference created successfully.");
        } catch (\Exception $e) {
            // Rollback the transaction if an error occurs
            DB::rollBack();

            Log::error($e);

            // Return error response
            return $this->errorResponse(500, 'Failed to add new preference. Please try again later.');
        }
    }

    public function update(Request $request, $profileId)
    {
        // Find the existing preference based on profile_id
        $preference = Preference::where('profile_id', $profileId)->first();
        if (!$preference) {
            return $this->errorResponse('Preference not found', 404);
        }

        // Validate the request data
        $validator = Validator::make($request->all(), [
            'content_type' => [
                'sometimes',
                'string',
                'max:255',
                Rule::in([
                    '18+', 'For Kids', 'Includes Violence', 'Includes Sex',
                    'Family Friendly', 'Educational', 'Sci-Fi Themes', 'Fantasy Elements',
                ]),
            ],
            'content_preference' => [
                'sometimes',
                'string',
                'max:255',
                Rule::in(['movies', 'series', 'both']),
            ],
            'genre' => [
                'sometimes',
                'array',
                function ($attribute, $value, $fail) {
                    $allowedGenres = [
                        'Action', 'Comedy', 'Drama', 'Horror', 'Thriller',
                        'Fantasy', 'Science Fiction', 'Romance', 'Documentary',
                        'Animation', 'Crime', 'Mystery', 'Adventure', 'Western', 'Biographical',
                    ];
                    foreach ($value as $genre) {
                        if (!in_array($genre, $allowedGenres)) {
                            return $fail("The $attribute contains an invalid genre: $genre.");
                        }
                    }
                },
            ],
            'genre.*' => 'string|max:255',
            'minimum_age' => 'sometimes|integer|min:0',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(400, $validator->errors()->first());
        }

        $validated = $validator->safe()->toArray();

        // Convert 'genre' to JSON if it exists
        if (isset($validated['genre'])) {
            $validated['genre'] = json_encode($validated['genre']);
        }

        // Start database transaction
        DB::beginTransaction();

        try {
            // Update the preference record in the database
            $preference->update($validated);

            // Commit the transaction after successful update
            DB::commit();

            // Return the response with updated preference data
            return $this->dataResponse([
                'preference' => $preference->only(['preference_id', 'profile_id', 'content_type', 'content_preference', 'genre', 'minimum_age']),
            ], "Preference updated successfully.");
        } catch (\Exception $e) {
            // Roll back the transaction in case of failure
            DB::rollBack();

            Log::error($e);
            // Return error response
            return $this->errorResponse(500, 'Failed to update preference. Please try again later.');
        }
    }

    public function destroy($profileId)
    {
        // Find the existing preference based on profile_id
        $preference = Preference::where('profile_id', $profileId)->first();
        if (!$preference) {
            return $this->errorResponse('Preference not found', 404);
        }
        $preference->delete();
        return $this->messageResponse('Preference deleted successfully.', 200);
    }
}
