<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use Illuminate\Http\Request;

class MovieController extends Controller
{
    /**
     * Validation rules for creating a movie
     */
    private function storeRules()
    {
        return [
            'title' => 'required|string|max:100',
            'duration' => ['required', 'regex:/^\d{2}:\d{2}:\d{2}$/'],
            'release_date' => 'required|date',
            'quality' => 'required|array',
            'age_restriction' => 'required|integer|min:0',
            'genre' => 'required|array',
            'viewing_classification' => 'required|string',
            'available_languages' => 'required|array',
        ];
    }

    /**
     * Validation rules for updating a movie
     */
    private function updateRules()
    {
        return [
            'title' => 'sometimes|string|max:100',
            'duration' => ['sometimes', 'regex:/^\d{2}:\d{2}:\d{2}$/'],
            'release_date' => 'sometimes|date',
            'quality' => 'sometimes|array',
            'age_restriction' => 'sometimes|integer|min:0',
            'genre' => 'sometimes|array',
            'viewing_classification' => 'sometimes|string',
            'available_languages' => 'sometimes|array',
        ];
    }

    /**
     * Encode array fields to JSON
     */
    private function encodeArrayFields($data)
    {
        if (isset($data['quality'])) {
            $data['quality'] = json_encode($data['quality']);
        }
        if (isset($data['genre'])) {
            $data['genre'] = json_encode($data['genre']);
        }
        if (isset($data['available_languages'])) {
            $data['available_languages'] = json_encode($data['available_languages']);
        }
        return $data;
    }

    /**
     * Display a listing of the movies.
     */
    public function index()
    {
        $movies = Movie::all();
        return response()->json($movies, 200);
    }

    /**
     * Store a newly created movie in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate($this->storeRules());
        $validated = $this->encodeArrayFields($validated);
        
        $movie = Movie::create($validated);
        return response()->json($movie, 201);
    }

    /**
     * Display the specified movie.
     */
    public function show($id)
    {
        $movie = Movie::findOrFail($id);
        return response()->json($movie, 200);
    }

    /**
     * Update the specified movie in storage.
     */
    public function update(Request $request, $id)
    {
        $movie = Movie::findOrFail($id);
        
        $validated = $request->validate($this->updateRules());
        $validated = $this->encodeArrayFields($validated);

        $movie->update($validated);
        return response()->json($movie, 200);
    }

    /**
     * Remove the specified movie from storage.
     */
    public function destroy($id)
    {
        $movie = Movie::findOrFail($id);
        $movie->delete();
        return response()->json(['message' => 'Movie deleted successfully'], 200);
    }
}
