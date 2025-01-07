<?php

namespace App\Http\Controllers\Api;

use App\Models\Episode;
use App\Models\Season;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Api\BaseController;

class EpisodeController extends BaseController
{
    // Store a new episode
    public function store(Request $request, $seriesId, $seasonId)
    {
        $validated = validator($request->all(), [
            'episode_number' => 'required|integer',
            'title' => 'required|string|max:255',
            'quality' => 'required|string',
            'duration' => 'required|integer',
            'available_languages' => 'required|string',
            'release_date' => 'required|date',
            'viewing_classification' => 'required|string',
        ])->validate();

        $season = Season::findOrFail($seasonId);
        $episode = $season->episodes()->create($validated);

        return $this->successResponse($episode, 'Episode created successfully');
    }

    // Update an existing episode
    public function update(Request $request, $seriesId, $seasonId, $episodeId)
    {
        $validated = validator($request->all(), [
            'episode_number' => 'sometimes|integer',
            'title' => 'sometimes|string|max:255',
            'quality' => 'sometimes|string',
            'duration' => 'sometimes|integer',
            'available_languages' => 'sometimes|string',
            'release_date' => 'sometimes|date',
            'viewing_classification' => 'sometimes|string',
        ])->validate();

        $episode = Episode::findOrFail($episodeId);
        $episode->update($validated);

        return $this->successResponse($episode, 'Episode updated successfully');
    }

    // Delete an episode
    public function destroy($seriesId, $seasonId, $episodeId)
    {
        $episode = Episode::findOrFail($episodeId);
        $episode->delete();

        return $this->messageResponse('Episode deleted successfully', 204);
    }
}
