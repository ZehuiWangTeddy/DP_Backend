<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\Preference;
use Illuminate\Http\Request;

class PreferenceController extends BaseController
{
    public function index($id)
    {
        $preferences = Preference::where('profile_id', $id)->get();
        return $this->dataResponse($preferences, 'Preferences retrieved successfully');
    }

    public function store(Request $request, $id)
    {
        $validatedData = $request->validate([
            'content_type' => 'required|string|max:255',
            'content_preference' => 'nullable|string|max:255',
            'genre' => 'required|string|max:255',
            'minimum_age' => 'required|integer|min:0',
        ]);

        $validatedData['profile_id'] = $id;

        $preference = Preference::create($validatedData);

        return $this->dataResponse($preference, 'Preference created successfully');
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

        $preference = Preference::where('profile_id', $id)
            ->where('preference_id', $validatedData['preference_id'])
            ->first();

        if (!$preference) {
            return $this->errorResponse('Preference not found', 404);
        }

        $preference->update($validatedData);

        return $this->dataResponse($preference, 'Preference updated successfully');
    }
}
