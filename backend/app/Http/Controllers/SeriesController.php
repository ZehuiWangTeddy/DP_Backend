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

        // Create a new series
        $series = Series::create($validated);

        return response()->json(['message' => 'Series created successfully', 'series' => $series], 201);
    }

    /**
     * Display the specified series.
     */
    public function show($id)
    {
        $series = Series::find($id);

        if (!$series) {
            return response()->json(['message' => 'Series not found'], 404);
        }

        return response()->json($series, 200);
    }

    /**
     * Update the specified series in storage.
     */
    public function update(Request $request, $id)
    {
        $series = Series::find($id);

        if (!$series) {
            return response()->json(['message' => 'Series not found'], 404);
        }

        // Validate the incoming request
        $validated = $request->validate([
            'title' => 'sometimes|string|max:100',
            'release_date' => 'sometimes|date',
            'age_restriction' => 'sometimes|integer|min:0',
            'genre' => 'sometimes|array',
            'viewing_classification' => 'sometimes|string',
            'available_languages' => 'sometimes|array',
        ]);

        // Update the series
        $series->update($validated);

        return response()->json(['message' => 'Series updated successfully', 'series' => $series], 200);
    }

    /**
     * Remove the specified series from storage.
     */
    public function destroy($id)
    {
        $series = Series::find($id);

        if (!$series) {
            return response()->json(['message' => 'Series not found'], 404);
        }

        // Delete the series
        $series->delete();

        return response()->json(['message' => 'Series deleted successfully'], 200);
    }
}
