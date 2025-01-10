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
            'quality' => $isUpdate ? 'sometimes|array' : 'required|array',
            'age_restriction' => $isUpdate ? 'sometimes|integer|min:0' : 'required|integer|min:0',
            'genre' => $isUpdate ? 'sometimes|array' : 'required|array',
            'viewing_classification' => $isUpdate ? 'sometimes|string' : 'required|string',
            'available_languages' => $isUpdate ? 'sometimes|array' : 'required|array',
            'file' => $isUpdate ? 'sometimes|file|mimes:mp4|max:20480' : 'nullable|file|mimes:mp4|max:20480',
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
        try {
            $validated = $this->validateMovie($request);
            $validated = $this->encodeFields($validated);

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $safeName = time() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "", $file->getClientOriginalName());
                $path = $file->storeAs('media/movies', $safeName, 'public');
                $validated['file_path'] = $path;
            }

            $movie = Movie::create($validated);
            return response()->json([
                'data' => $movie,
                'url' => isset($path) ? Storage::url($path) : null,
                'message' => 'Movie created successfully'
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create movie: ' . $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $movie = Movie::findOrFail($id);
        return response()->json(['data' => $movie, 'message' => 'Movie retrieved successfully'], 200);
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
            return response()->json(['data' => $movie, 'message' => 'Movie updated successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update movie: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $movie = Movie::findOrFail($id);

            if ($movie->file_path && Storage::exists($movie->file_path)) {
                Storage::delete($movie->file_path);
            }

            $movie->delete();
            return response()->json(['message' => 'Movie deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete movie: ' . $e->getMessage()], 500);
        }
    }
}
