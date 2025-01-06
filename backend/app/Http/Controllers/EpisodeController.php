<?php

namespace App\Http\Controllers;

use App\Models\Episode;
use App\Models\Season;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EpisodeController extends Controller
{
    public function store(Request $request, $seriesId, $seasonId)
    {
        $validated = $request->validate([
            'episode_number' => 'required|integer',
            'title' => 'required|string|max:255',
            'quality' => 'required|string',
            'duration' => 'required|integer',
            'available_languages' => 'required|string',
            'release_date' => 'required|date',
            'viewing_classification' => 'required|string',
        ]);

        $season = Season::findOrFail($seasonId);
        $validated['season_id'] = $seasonId;
        
        $episode = Episode::create($validated);

        return response()->json([
            'message' => 'Episode created successfully',
            'data' => $episode
        ], 201);
    }

    public function update(Request $request, $seriesId, $seasonId, $episodeId)
    {
        $validated = $request->validate([
            'episode_number' => 'sometimes|integer',
            'title' => 'sometimes|string|max:255',
            'quality' => 'sometimes|string',
            'duration' => 'sometimes|integer',
            'available_languages' => 'sometimes|string',
            'release_date' => 'sometimes|date',
            'viewing_classification' => 'sometimes|string',
        ]);

        $episode = Episode::findOrFail($episodeId);
        $episode->update($validated);

        return response()->json([
            'message' => 'Episode updated successfully',
            'data' => $episode
        ], 200);
    }

    public function destroy($seriesId, $seasonId, $episodeId)
    {
        $episode = Episode::findOrFail($episodeId);
        $episode->delete();

        return response()->json([
            'message' => 'Episode deleted successfully'
        ], 204);
    }
} 