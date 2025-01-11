<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\Series;
use Illuminate\Http\Request;

class SeriesController extends BaseController
{
    public function index()
    {
        $series = Series::all();
        return $this->dataResponse($series, 'Series retrieved successfully');
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
        return $this->dataResponse($series, 'Series created successfully');
    }

    public function show($id)
    {
        $series = Series::findOrFail($id);
        return $this->dataResponse($series, 'Series retrieved successfully');
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

        return $this->dataResponse($series, 'Series updated successfully');
    }

    public function destroy($id)
    {
        $series = Series::find($id);
        if (!$series) {
            return $this->errorResponse(404, 'Series not found');
        }
        $series->delete();
        return $this->messageResponse('Series deleted successfully', 201);
    }

    public function search(Request $request)
    {
        $validated = $request->validate([
            'query' => 'required|string|min:2'
        ]);

        $series = Series::where('title', 'LIKE', '%' . $validated['query'] . '%')->get();
        return $this->dataResponse($series, 'Search results retrieved successfully');
    }

    public function getByGenre(Request $request)
    {
        $validated = $request->validate([
            'genre' => 'required|string'
        ]);

        $series = Series::whereJsonContains('genre', $validated['genre'])->get();
        return $this->dataResponse($series, 'Series retrieved by genre successfully');
    }

    public function getByAgeRestriction(Request $request)
    {
        $validated = $request->validate([
            'age' => 'required|integer|min:0'
        ]);

        $series = Series::where('age_restriction', '<=', $validated['age'])->get();
        return $this->dataResponse($series, 'Series retrieved by age restriction successfully');
    }

    public function getByLanguage(Request $request)
    {
        $validated = $request->validate([
            'language' => 'required|string'
        ]);

        $series = Series::whereJsonContains('available_languages', $validated['language'])->get();
        return $this->dataResponse($series, 'Series retrieved by language successfully');
    }

    public function getLatest()
    {
        $series = Series::orderBy('release_date', 'desc')->take(10)->get();
        return $this->dataResponse($series, 'Latest series retrieved successfully');
    }
}
