<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\Episode;
use App\Models\Movie;
use App\Models\Subtitle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SubtitleController extends BaseController
{
    // Store a new subtitle
    public function store(Request $request, $episodeId = null, $id = null)
    {
        $validator = Validator::make($request->all(), [
            'language' => 'required|string',
            'file' => 'required|mimes:vtt,srt',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $subtitle = new Subtitle();
        $subtitle->language = $request->language;
        $subtitle->file_path = $request->file('file')->store('subtitles');

        if ($episodeId) {
            $episode = Episode::findOrFail($episodeId);
            $episode->subtitles()->save($subtitle);
        } elseif ($id) {
            $movie = Movie::findOrFail($id);
            $movie->subtitles()->save($subtitle);
        }

        return response()->json(['message' => 'Subtitle successfully added.'], 201);
    }

    // Retrieve all subtitles for a specific episode or movie
    public function index($episodeId = null, $id = null)
    {
        if ($episodeId) {
            $episode = Episode::findOrFail($episodeId);
            $subtitles = $episode->subtitles;
        } elseif ($id) {
            $movie = Movie::findOrFail($id);
            $subtitles = $movie->subtitles;
        } else {
            return response()->json(['message' => 'Episode or Movie not specified.'], 400);
        }

        return response()->json(['data' => $subtitles, 'message' => 'Episode retrieved successfully'], 200);
    }

    // Update an existing subtitle
    public function update(Request $request, $subtitleId)
    {
        $validator = Validator::make($request->all(), [
            'language' => 'sometimes|required|string',
            'file' => 'sometimes|required|mimes:vtt,srt',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $subtitle = Subtitle::findOrFail($subtitleId);

        if ($request->has('language')) {
            $subtitle->language = $request->language;
        }
        if ($request->hasFile('file')) {
            $subtitle->file_path = $request->file('file')->store('subtitles');
        }

        $subtitle->save();

        return response()->json(['message' => 'Subtitle successfully updated.']);
    }

    // Delete a subtitle
    public function destroy($subtitleId)
    {
        $subtitle = Subtitle::findOrFail($subtitleId);
        $subtitle->delete();

        return response()->json(['message' => 'Subtitle successfully deleted.']);
    }
}
