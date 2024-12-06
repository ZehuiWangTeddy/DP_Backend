<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use Illuminate\Http\Request;

class MovieController extends Controller
{
    /**
     * Display a listing of the movies.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $movies = Movie::all();
        return response()->json($movies, 200);
    }

    /**
     * Store a newly created movie in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:100',
            'duration' => ['required', 'regex:/^\d{2}:\d{2}:\d{2}$/'],
            'release_date' => 'required|date',
            'quality' => 'required|array',
            'age_restriction' => 'required|integer|min:0',
            'genre' => 'required|array',
            'viewing_classification' => 'required|string',
            'available_languages' => 'required|array',
        ]);

        // Encode array fields to JSON
        $validated['quality'] = json_encode($validated['quality']);
        $validated['genre'] = json_encode($validated['genre']);
        $validated['available_languages'] = json_encode($validated['available_languages']);

        $movie = Movie::create($validated);
        return response()->json($movie, 201);
    }

    /**
     * Display the specified movie.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $movie = Movie::findOrFail($id);
        return response()->json($movie, 200);
    }

    /**
     * Update the specified movie in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $movie = Movie::findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:100',
            'duration' => ['sometimes', 'regex:/^\d{2}:\d{2}:\d{2}$/'],
            'release_date' => 'sometimes|date',
            'quality' => 'sometimes|array',
            'age_restriction' => 'sometimes|integer|min:0',
            'genre' => 'sometimes|array',
            'viewing_classification' => 'sometimes|string',
            'available_languages' => 'sometimes|array',
        ]);

        // Encode array fields to JSON if they are present
        if (isset($validated['quality'])) {
            $validated['quality'] = json_encode($validated['quality']);
        }
        if (isset($validated['genre'])) {
            $validated['genre'] = json_encode($validated['genre']);
        }
        if (isset($validated['available_languages'])) {
            $validated['available_languages'] = json_encode($validated['available_languages']);
        }

        $movie->update($validated);
        return response()->json($movie, 200);
    }

    /**
     * Remove the specified movie from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $movie = Movie::findOrFail($id);
        $movie->delete();
        return response()->json(['message' => 'Movie deleted successfully'], 200);
    }
}
