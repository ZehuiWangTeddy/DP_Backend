<?php

namespace App\Http\Controllers\Api;

use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends BaseController
{
    public function index(): \Illuminate\Http\JsonResponse
    {
        $profiles = Profile::with(['user', 'preference'])->get();
        return $this->dataResponse($profiles);
    }

    public function show($id): \Illuminate\Http\JsonResponse
    {
        $profile = Profile::with(['user', 'preference'])->findOrFail($id);
        return $this->dataResponse($profile);
    }

    public function update(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'photo_path' => 'nullable|string',
            'child_profile' => 'required|boolean',
            'date_of_birth' => 'nullable|date',
            'language' => 'nullable|string|max:255',
        ]);

        $profile = Profile::findOrFail($id);

        // Update attributes manually
        foreach ($validated as $key => $value) {
            $profile->{$key} = $value;
        }
        $profile->save();

        $profile->load('user', 'preference');
        return $this->dataResponse($profile, 'Profile updated successfully');
    }

    public function destroy($id): \Illuminate\Http\JsonResponse
    {
        $profile = Profile::findOrFail($id);
        $profile->delete();

        return $this->messageResponse('Profile deleted successfully');
    }

    public function createProfile(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();

        // Check if the user already has 4 profiles
        $profileCount = Profile::where('user_id', $user->user_id)->count();
        if ($profileCount >= 4) {
            return $this->errorResponse(400, 'You can only have a maximum of 4 profiles.');
        }

        // Validate the incoming data
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'photo_path' => 'nullable|string|max:255',
            'child_profile' => 'required|boolean',
            'date_of_birth' => 'nullable|date',
            'language' => 'nullable|string|max:20',
        ]);

        // Create the new profile
        $profile = Profile::create([
            'user_id' => $user->user_id,
            'name' => $validated['name'],
            'photo_path' => $validated['photo_path'] ?? null,
            'child_profile' => $validated['child_profile'],
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'language' => $validated['language'] ?? 'en',
        ]);

        return $this->dataResponse($profile, 'Profile created successfully.');
    }
}
