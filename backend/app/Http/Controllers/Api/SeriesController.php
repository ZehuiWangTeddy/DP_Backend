<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\Series;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SeriesController extends BaseController
{
    public function index()
    {
        $series = Series::all();
        return $this->dataResponse($series, 'Series retrieved successfully');
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:100',
                'release_date' => 'required|date',
                'age_restriction' => 'required|integer|min:0',
                'genre' => 'required|array|in:Action,Comedy,Drama,Horror,Thriller,Fantasy,Science Fiction,Romance,Documentary,Animation,Crime,Mystery,Adventure,Western,Biographical',
                'viewing_classification' => 'required|string|in:18+,For Kids,Includes Violence,Includes Sex,Family Friendly,Educational,Sci-Fi Themes,Fantasy Elements',
                'available_languages' => 'required|array',
            ]);

            foreach (['genre', 'available_languages'] as $field) {
                $validated[$field] = json_encode($validated[$field]);
            }

            $series = Series::create($validated);
            return $this->dataResponse($series, 'Series created successfully');
        } catch (ValidationException $e) {
            return $this->errorResponse(400, 'Please check your input ' . $e->getMessage());
        } catch (\Exception $e) {
            return $this->errorResponse(500, 'An unexpected error occurred: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        try {
            $series = Series::findOrFail($id);
            return $this->dataResponse($series, 'Series retrieved successfully');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse(404, 'Series not found');
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $series = Series::find($id);
            if (!$series) {
                return $this->errorResponse(404, 'Series not found');
            }

            $validated = $request->validate([
                'title' => 'sometimes|string|max:100',
                'release_date' => 'sometimes|date',
                'age_restriction' => 'sometimes|integer|min:0',
                'genre' => 'sometimes|array|in:Action,Comedy,Drama,Horror,Thriller,Fantasy,Science Fiction,Romance,Documentary,Animation,Crime,Mystery,Adventure,Western,Biographical',
                'viewing_classification' => 'sometimes|string|in:18+,For Kids,Includes Violence,Includes Sex,Family Friendly,Educational,Sci-Fi Themes,Fantasy Elements',
                'available_languages' => 'sometimes|array',
            ]);

            foreach (['genre', 'available_languages'] as $field) {
                if (isset($validated[$field])) {
                    $validated[$field] = json_encode($validated[$field]);
                }
            }

            $series->update($validated);
            return $this->dataResponse($series, 'Series updated successfully');
        } catch (ValidationException $e) {
            return $this->errorResponse(400, 'Please check your input ' . $e->getMessage());
        } catch (\Exception $e) {
            return $this->errorResponse(500, 'An unexpected error occurred: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $series = Series::find($id);
        if (!$series) {
            return $this->errorResponse(404, 'Series not found');
        }
        $series->delete();
        return $this->messageResponse('Series deleted successfully', 200);
    }

    public function search(Request $request)
    {
        try {
            $validated = $request->validate([
                'query' => 'required|string|min:2'
            ]);
        } catch(\Exception $e) {
            return $this->errorResponse(400, $e->getMessage());
        }

        $series = Series::where('title', 'LIKE', '%' . $validated['query'] . '%')->get();
        return $this->dataResponse($series, 'Search results retrieved successfully');
    }

    public function getByGenre(Request $request)
    {
        try {
            $validated = $request->validate([
                'genre' => 'required|string'
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse(400, $e->getMessage());
        }


        $series = Series::whereJsonContains('genre', $validated['genre'])->get();
        return $this->dataResponse($series, 'Series retrieved by genre successfully');
    }

    public function getByAgeRestriction(Request $request)
    {
        try {
            $validated = $request->validate([
                'age' => 'required|integer|min:0'
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse(400, $e->getMessage());
        }


        $series = Series::where('age_restriction', '<=', $validated['age'])->get();
        return $this->dataResponse($series, 'Series retrieved by age restriction successfully');
    }

    public function getByLanguage(Request $request)
    {
        try {
            $validated = $request->validate([
                'language' => 'required|string'
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse(400, $e->getMessage());
        }

        $series = Series::whereJsonContains('available_languages', $validated['language'])->get();
        return $this->dataResponse($series, 'Series retrieved by language successfully');
    }

    public function getLatest()
    {
        $series = Series::orderBy('release_date', 'desc')->take(10)->get();
        return $this->dataResponse($series, 'Latest series retrieved successfully');
    }
}
