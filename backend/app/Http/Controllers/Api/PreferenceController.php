<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Preference;

class PreferenceController extends Controller
{
    public function index($id)
    {
        $preferences = Preference::where('profile_id', $id)->get();
        return response()->json(['data' => $preferences, 'message' => 'Preferences retrieved successfully'], 200);
    }

    public function store(Request $request, $id)
    {
        $validatedData = $request->validate([
            'content_type' => 'required|string|max:255',
            'content_preference' => 'nullable|string|max:255', 
            'genre' => 'required|string|max:255',
            'minimum_age' => 'required|integer|min:0',
        ]);

        $validatedData['profile_id'] = $id; // Assign profile ID

        $preference = Preference::create($validatedData);

        return response()->json(['data' => $preference, 'message' => 'Preference created successfully'], 201);
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'preference_id' => 'required|integer',
            'content_type' => 'required|string|max:255',
            'content_preference' => 'nullable|string|max:255', 
            'genre' => 'required|string|max:255',
            'minimum_age' => 'required|integer|min:0',
        ]);

        // Retrieve the specific preference via ID to ensure it belongs to the right profile
        $preference = Preference::where('profile_id', $id)
            ->where('preference_id', $validatedData['preference_id'])
            ->first();

        if (!$preference) {
            return response()->json(['message' => 'Preference not found'], 404);
        }

        // Update preference with validated data
        $preference->update($validatedData);

        return response()->json(['data' => $preference, 'message' => 'Preference updated successfully'], 200);
    }
}
