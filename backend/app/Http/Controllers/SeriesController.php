<?php

namespace App\Http\Controllers;

use App\Models\Series;
use Illuminate\Http\Request;

class SeriesController extends Controller
{
    /**
     * Display a listing of the series.
     */
    public function index()
    {
        $series = Series::all(); // Retrieve all series
        return response()->json($series, 200);
    }

    /**
     * Store a newly created series in storage.
     */
    public function store(Request $request)
    {
        // Validate the incoming request
        $validated = $request->validate([
            'title' => 'required|string|max:100',
            'release_date' => 'required|date',
            'age_restriction' => 'required|integer|min:0',
            'genre' => 'required|array', // Ensure genre is an array
            'viewing_classification' => 'required|string',
            'available_languages' => 'required|array', // Ensure it's an array
        ]);

        // Encode array fields to JSON
        $validated['genre'] = json_encode($validated['genre']);
        $validated['available_languages'] = json_encode($validated['available_languages']);

        // Create a new series
        $series = Series::create($validated);

        return response()->json(['message' => 'Series created successfully', 'series' => $series], 201);
    }

    /**
     * Display the specified series.
     */
    public function show($id)
    {
        $series = Series::findOrFail($id);
        return response()->json($series, 200);
    }

    /**
     * Update the specified series in storage.
     */
    public function update(Request $request, $id)
    {
        $series = Series::findOrFail($id);

        // Validate the incoming request
        $validated = $request->validate([
            'title' => 'sometimes|string|max:100',
            'release_date' => 'sometimes|date',
            'age_restriction' => 'sometimes|integer|min:0',
            'genre' => 'sometimes|array',
            'viewing_classification' => 'sometimes|string',
            'available_languages' => 'sometimes|array',
        ]);

        // Encode array fields to JSON if they are present
        if (isset($validated['genre'])) {
            $validated['genre'] = json_encode($validated['genre']);
        }
        if (isset($validated['available_languages'])) {
            $validated['available_languages'] = json_encode($validated['available_languages']);
        }

        // Update the series
        $series->update($validated);

        return response()->json(['message' => 'Series updated successfully', 'series' => $series], 200);
    }

    /**
     * Remove the specified series from storage.
     */
    public function destroy($id)
    {
        $series = Series::findOrFail($id);
        $series->delete();
        return response()->json(['message' => 'Series deleted successfully'], 200);
    }

    /**
     * Search series by title.
     */
    public function search(Request $request)
    {
        $validated = $request->validate([
            'query' => 'required|string|min:2'
        ]);

        $series = Series::where('title', 'LIKE', '%' . $validated['query'] . '%')->get();
        return response()->json($series, 200);
    }

    /**
     * Get series by genre.
     */
    public function getByGenre(Request $request)
    {
        $validated = $request->validate([
            'genre' => 'required|string'
        ]);

        $series = Series::whereJsonContains('genre', $validated['genre'])->get();
        return response()->json($series, 200);
    }

    /**
     * Get series by age restriction.
     */
    public function getByAgeRestriction(Request $request)
    {
        $validated = $request->validate([
            'age' => 'required|integer|min:0'
        ]);

        $series = Series::where('age_restriction', '<=', $validated['age'])->get();
        return response()->json($series, 200);
    }

    /**
     * Get series by language.
     */
    public function getByLanguage(Request $request)
    {
        $validated = $request->validate([
            'language' => 'required|string'
        ]);

        $series = Series::whereJsonContains('available_languages', $validated['language'])->get();
        return response()->json($series, 200);
    }

    /**
     * Get latest series.
     */
    public function getLatest()
    {
        $series = Series::orderBy('release_date', 'desc')->take(10)->get();
        return response()->json($series, 200);
    }
}
