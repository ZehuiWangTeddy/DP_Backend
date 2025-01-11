<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\Movie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MovieController extends BaseController
{
    private function validateMovie(Request $request, $isUpdate = false)
    {
        $rules = [
            'title' => $isUpdate ? 'sometimes|string|max:100' : 'required|string|max:100',
            'duration' => $isUpdate ? 'sometimes|regex:/^\d{2}:\d{2}:\d{2}$/' : 'required|regex:/^\d{2}:\d{2}:\d{2}$/',
            'release_date' => $isUpdate ? 'sometimes|date' : 'required|date',
            'quality' => $isUpdate ? 'sometimes|array|in:SD,HD,UHD' : 'required|array|in:SD,HD,UHD',
            'age_restriction' => $isUpdate ? 'sometimes|integer|min:0' : 'required|integer|min:0',
            'genre' => $isUpdate ? 'sometimes|array|in:Action,Comedy,Drama,Horror,Thriller,Fantasy,Science Fiction,Romance,Documentary,Animation,Crime,Mystery,Adventure,Western,Biographical' : 'required|array|in:Action,Comedy,Drama,Horror,Thriller,Fantasy,Science Fiction,Romance,Documentary,Animation,Crime,Mystery,Adventure,Western,Biographical',
            'viewing_classification' => $isUpdate ? 'sometimes|string|in:18+,For Kids,Includes Violence,Includes Sex,Family Friendly,Educational,Sci-Fi Themes,Fantasy Elements' : 'required|string|in:18+,For Kids,Includes Violence,Includes Sex,Family Friendly,Educational,Sci-Fi Themes,Fantasy Elements',
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
        return $this->dataResponse($movies, 'Movies retrieved successfully');
    }

    public function store(Request $request)
    {
        try {
            $validated = $this->validateMovie($request);
            $validated = $this->encodeFields($validated);

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $safeName = time() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "", $file->getClientOriginalName());
            }

            $movie = Movie::create($validated);

            return $this->dataResponse([
                'data' => $movie,
            ], "Movie created successfully");
        } catch (\Exception $e) {
            return $this->errorResponse(500, 'Failed to create movie: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $movie = Movie::findOrFail($id);
        if (!$movie) {
            return $this->errorResponse(404, 'Movie not found');
        }
        return $this->dataResponse($movie, 'Movie retrieved successfully');
    }

    public function update(Request $request, $id)
    {
        try {
            $movie = Movie::findOrFail($id);
            $validated = $this->validateMovie($request, true);
            $validated = $this->encodeFields($validated);

            if ($request->hasFile('file')) {
                // Delete old file if it exists
                if ($movie->file_path && Storage::exists($movie->file_path)) {
                    Storage::delete($movie->file_path);
                }

                $file = $request->file('file');
                $safeName = time() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "", $file->getClientOriginalName());
                $path = $file->storeAs('media/movies', $safeName, 'public');
                $validated['file_path'] = $path;
            }

            $movie->update($validated);
            return $this->dataResponse($movie, 'Movie updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse(500, 'Failed to update movie: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $movie = Movie::find($id);
        if (!$movie) {
            return $this->errorResponse(404, 'Movie not found');
        }
        $movie->delete();
        return $this->messageResponse('Movie deleted successfully', 200);
    }
}
