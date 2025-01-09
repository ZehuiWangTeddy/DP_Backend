<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\Episode;
use App\Models\Movie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaController extends BaseController
{
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:jpg,png,mp4|max:20480',
            'type' => 'required|in:movie,show',
            'title' => 'required|string|max:255'
        ]);

        try {
            $file = $request->file('file');
            $safeName = time() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "", $file->getClientOriginalName());
            $path = $file->storeAs('media', $safeName, 'public');

            if ($request->input('type') === 'movie') {
                $media = Movie::create([
                    'title' => $request->input('title'),
                    'file_path' => $path,
                ]);
            } elseif ($request->input('type') === 'show') {
                $media = Episode::create([
                    'title' => $request->input('title'),
                    'file_path' => $path,
                ]);
            } else {
                throw new \Exception('Invalid media type');
            }

            return response()->json([
                'data' => $media,
                'url' => Storage::url($path),
                'message' => 'Media uploaded successfully'
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Upload failed: ' . $e->getMessage()], 500);
        }
    }

    public function getMedia(Request $request)
    {
        $type = $request->query('type'); // Accept 'movie' or 'show' as a query parameter

        try {
            $media = [];

            if ($type === 'movie') {
                $media = Movie::all();
            } elseif ($type === 'show') {
                $media = Episode::all();
            } else {
                $media = [
                    'movies' => Movie::all(),
                    'shows' => Episode::all()
                ];
            }

            return response()->json([
                'data' => $media,
                'message' => 'Media fetched successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch media: ' . $e->getMessage()], 500);
        }
    }

    public function delete($id)
    {
        try {
            $media = Movie::find($id) ?? Episode::find($id);

            if (!$media) {
                return response()->json(['error' => 'Media not found'], 404);
            }

            $path = $media->file_path;

            if (Storage::exists($path)) {
                Storage::delete($path);
            }

            $media->delete();

            return response()->json(['message' => 'Media deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete media: ' . $e->getMessage()], 500);
        }
    }
}
