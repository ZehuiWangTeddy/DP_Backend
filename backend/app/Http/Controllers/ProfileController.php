<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function index(): \Illuminate\Http\JsonResponse
    {
        $profiles = Profile::with(['user', 'preference'])->get();
        return response()->json($profiles);
    }

    public function show($id): \Illuminate\Http\JsonResponse
    {
        $profile = Profile::with(['user', 'preference'])->findOrFail($id);
        return response()->json($profile);
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

        $profile->update($validated);

        $profile->load('user', 'preference');
        return response()->json(['message' => 'Profile updated successfully', 'profile' => $profile]);
    }

    public function destroy($id): \Illuminate\Http\JsonResponse
    {
        $profile = Profile::findOrFail($id);
        $profile->delete();

        return response()->json(['message' => 'Profile deleted successfully']);
    }
}
