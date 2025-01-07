<?php

namespace App\Http\Controllers;

use App\Models\Series;
use Illuminate\Http\Request;

class SeriesController extends Controller
{
    public function index()
    {
        $series = Series::all();
        return response()->json(['data' => $series, 'message' => 'Series retrieved successfully'], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:100',
            'release_date' => 'required|date',
            'age_restriction' => 'required|integer|min:0',
            'genre' => 'required|array',
            'viewing_classification' => 'required|string',
            'available_languages' => 'required|array',
        ]);

        foreach (['genre', 'available_languages'] as $field) {
            $validated[$field] = json_encode($validated[$field]);
        }

        $series = Series::create($validated);
        return response()->json(['data' => $series, 'message' => 'Series created successfully'], 201);
    }

    public function show($id)
    {
        $series = Series::findOrFail($id);
        return response()->json(['data' => $series, 'message' => 'Series retrieved successfully'], 200);
    }

    public function update(Request $request, $id)
    {
        $series = Series::findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:100',
            'release_date' => 'sometimes|date',
            'age_restriction' => 'sometimes|integer|min:0',
            'genre' => 'sometimes|array',
            'viewing_classification' => 'sometimes|string',
            'available_languages' => 'sometimes|array',
        ]);

        foreach (['genre', 'available_languages'] as $field) {
            if (isset($validated[$field])) {
                $validated[$field] = json_encode($validated[$field]);
            }
        }

        $series->update($validated);
        return response()->json(['data' => $series, 'message' => 'Series updated successfully'], 200);
    }

    public function destroy($id)
    {
        $series = Series::findOrFail($id);
        $series->delete();
        return response()->json(['message' => 'Series deleted successfully'], 200);
    }

    public function search(Request $request)
    {
        $validated = $request->validate([
            'query' => 'required|string|min:2'
        ]);

        $series = Series::where('title', 'LIKE', '%' . $validated['query'] . '%')->get();
        return response()->json(['data' => $series, 'message' => 'Search results retrieved successfully'], 200);
    }

    public function getByGenre(Request $request)
    {
        $validated = $request->validate([
            'genre' => 'required|string'
        ]);

        $series = Series::whereJsonContains('genre', $validated['genre'])->get();
        return response()->json(['data' => $series, 'message' => 'Series retrieved by genre successfully'], 200);
    }

    public function getByAgeRestriction(Request $request)
    {
        $validated = $request->validate([
            'age' => 'required|integer|min:0'
        ]);

        $series = Series::where('age_restriction', '<=', $validated['age'])->get();
        return response()->json(['data' => $series, 'message' => 'Series retrieved by age restriction successfully'], 200);
    }

    public function getByLanguage(Request $request)
    {
        $validated = $request->validate([
            'language' => 'required|string'
        ]);

        $series = Series::whereJsonContains('available_languages', $validated['language'])->get();
        return response()->json(['data' => $series, 'message' => 'Series retrieved by language successfully'], 200);
    }

    public function getLatest()
    {
        $series = Series::orderBy('release_date', 'desc')->take(10)->get();
        return response()->json(['data' => $series, 'message' => 'Latest series retrieved successfully'], 200);
    }
}
