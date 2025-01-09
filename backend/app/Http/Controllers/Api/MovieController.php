<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use Illuminate\Http\Request;

class MovieController extends Controller
{
    private function validateMovie(Request $request, $isUpdate = false)
    {
        $rules = [
            'title' => $isUpdate ? 'sometimes|string|max:100' : 'required|string|max:100',
            'duration' => $isUpdate ? 'sometimes|regex:/^\d{2}:\d{2}:\d{2}$/' : 'required|regex:/^\d{2}:\d{2}:\d{2}$/',
            'release_date' => $isUpdate ? 'sometimes|date' : 'required|date',
            'quality' => $isUpdate ? 'sometimes|array' : 'required|array',
            'age_restriction' => $isUpdate ? 'sometimes|integer|min:0' : 'required|integer|min:0',
            'genre' => $isUpdate ? 'sometimes|array' : 'required|array',
            'viewing_classification' => $isUpdate ? 'sometimes|string' : 'required|string',
            'available_languages' => $isUpdate ? 'sometimes|array' : 'required|array',
        ];

        return $request->validate($rules);
    }

    private function encodeFields($data)
    {
        foreach (['quality', 'genre', 'viewing_classification', 'available_languages'] as $field) {
            if (isset($data[$field])) {
                $data[$field] = json_encode($data[$field]);
            }
        }
        return $data;
    }

    public function index()
    {
        $movies = Movie::all();
        return response()->json(['data' => $movies, 'message' => 'Movies retrieved successfully'], 200);
    }

    public function store(Request $request)
    {
        $validated = $this->validateMovie($request);
        $validated = $this->encodeFields($validated);

        $movie = Movie::create($validated);
        return response()->json(['data' => $movie, 'message' => 'Movie created successfully'], 201);
    }

    public function show($id)
    {
        $movie = Movie::findOrFail($id);
        return response()->json(['data' => $movie, 'message' => 'Movie retrieved successfully'], 200);
    }

    public function update(Request $request, $id)
    {
        $movie = Movie::findOrFail($id);

        $validated = $this->validateMovie($request, true);
        $validated = $this->encodeFields($validated);

        $movie->update($validated);
        return response()->json(['data' => $movie, 'message' => 'Movie updated successfully'], 200);
    }

    public function destroy($id)
    {
        $movie = Movie::findOrFail($id);
        $movie->delete();

        return response()->json(['message' => 'Movie deleted successfully'], 200);
    }
}
