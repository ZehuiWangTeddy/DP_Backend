<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use Illuminate\Http\Request;

class MovieController extends Controller
{
    public function index()
    {
        return response()->json(Movie::all(), 200); // List all movies
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:100',
            'duration' => 'required|date_format:H:i:s',
            'release_date' => 'required|date',
            'quality' => 'required|json',
            'age_restriction' => 'required|integer|min:0',
            'genre' => 'required|json',
            'viewing_classification' => 'required|string',
            'available_languages' => 'required|json',
        ]);

        $movie = Movie::create($validated);
        return response()->json($movie, 201);
    }

    public function show($id)
    {
        $movie = Movie::findOrFail($id);
        return response()->json($movie, 200);
    }

    public function update(Request $request, $id)
    {
        $movie = Movie::findOrFail($id);
        $validated = $request->validate([
            'title' => 'string|max:100',
            'duration' => 'date_format:H:i:s',
            'release_date' => 'date',
            'quality' => 'json',
            'age_restriction' => 'integer|min:0',
            'genre' => 'json',
            'viewing_classification' => 'string',
            'available_languages' => 'json',
        ]);

        $movie->update($validated);
        return response()->json($movie, 200);
    }

    public function destroy($id)
    {
        $movie = Movie::findOrFail($id);
        $movie->delete();
        return response()->json(['message' => 'Movie deleted successfully'], 200);
    }
}
