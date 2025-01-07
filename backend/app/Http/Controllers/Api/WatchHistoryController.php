<?php

namespace App\Http\Controllers\Api;

use App\Models\Movie;
use App\Models\Episode;
use App\Models\WatchHistory;
use Illuminate\Http\Request;

class WatchHistoryController extends BaseController
{
    public function index(int $profileId)
    {
        $watchHistory = WatchHistory::where('profile_id', $profileId)->get();

        return $this->dataResponse($watchHistory, 'Watch history retrieved successfully');
    }

    public function startMovie(Request $request, $profileId, $movieId)
    {
        $movie = Movie::findOrFail($movieId);

        WatchHistory::create([
            'profile_id' => $profileId,
            'media_type' => 'movie',
            'media_id' => $movieId,
            'start_time' => now(),
            'end_time' => null,
        ]);

        return $this->messageResponse('Movie started successfully');
    }

    public function finishMovie(Request $request, $profileId, $movieId)
    {
        $watchHistory = WatchHistory::where('profile_id', $profileId)
                                    ->where('media_type', 'movie')
                                    ->where('media_id', $movieId)
                                    ->whereNull('end_time')
                                    ->firstOrFail();

        $watchHistory->update([
            'end_time' => now(),
        ]);

        return $this->messageResponse('Movie finished successfully');
    }

    public function startEpisode(Request $request, $profileId, $seriesId, $seasonId, $episodeId)
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

        return $this->messageResponse('Episode started successfully');
    }

    public function finishEpisode(Request $request, $profileId, $seriesId, $seasonId, $episodeId)
    {
        $watchHistory = WatchHistory::where('profile_id', $profileId)
                                    ->where('media_type', 'episode')
                                    ->where('media_id', $episodeId)
                                    ->whereNull('end_time')
                                    ->firstOrFail();

        $watchHistory->update([
            'end_time' => now(),
        ]);

        return $this->messageResponse('Episode finished successfully');
    }
}
