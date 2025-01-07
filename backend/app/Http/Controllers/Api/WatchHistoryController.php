<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\Movie;
use App\Models\Episode;
use App\Models\WatchHistory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class WatchHistoryController extends BaseController
{
    public function index(int $profileId): JsonResponse
    {
        $watchHistory = WatchHistory::where('profile_id', $profileId)->get();

        return response()->json($watchHistory, 200);
    }

    public function startMovie(Request $request, $profileId, $movieId): JsonResponse
    {
        $movie = Movie::findOrFail($movieId);

        WatchHistory::create([
            'profile_id' => $profileId,
            'media_type' => 'movie',
            'media_id' => $movieId,
            'start_time' => now(),
            'end_time' => null,
        ]);

        return response()->json(['message' => 'Movie started successfully'], 200);
    }

    public function finishMovie(Request $request, $profileId, $movieId): JsonResponse
    {
        $watchHistory = WatchHistory::where('profile_id', $profileId)
                                    ->where('media_type', 'movie')
                                    ->where('media_id', $movieId)
                                    ->whereNull('end_time')
                                    ->firstOrFail();

        $watchHistory->update([
            'end_time' => now(),
        ]);

        return response()->json(['message' => 'Movie finished successfully'], 200);
    }

    public function startEpisode(Request $request, $profileId, $seriesId, $seasonId, $episodeId): \Illuminate\Http\JsonResponse
    {
        $episode = Episode::findOrFail($episodeId);

        WatchHistory::create([
            'profile_id' => $profileId,
            'media_type' => 'episode',
            'media_id' => $episodeId,
            'series_id' => $seriesId,
            'season_id' => $seasonId,
            'start_time' => now(),
            'end_time' => null,
        ]);

        return response()->json(['message' => 'Episode started successfully'], 200);
    }

    public function finishEpisode(Request $request, $profileId, $seriesId, $seasonId, $episodeId): \Illuminate\Http\JsonResponse
    {
        $watchHistory = WatchHistory::where('profile_id', $profileId)
                                    ->where('media_type', 'episode')
                                    ->where('media_id', $episodeId)
                                    ->whereNull('end_time')
                                    ->firstOrFail();

        $watchHistory->update([
            'end_time' => now(),
        ]);

        return response()->json(['message' => 'Episode finished successfully'], 200);
    }
}
