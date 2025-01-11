<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\Episode;
use App\Models\Movie;
use App\Models\Profile;
use Illuminate\Http\Request;

class WatchListController extends BaseController
{
    public function index(int $profileId)
    {
        $profile = Profile::with(['watchList.movie', 'watchList.episode'])
            ->findOrFail($profileId);

        return $this->dataResponse($profile->watchList);
    }

    public function addMovie(Request $request, $profileId, $movieId)
    {
        try {
            $movie = Movie::findOrFail($movieId);
            $profile = Profile::findOrFail($profileId);

            $profile->watchList()->attach($movieId);
        } catch (\Exception $e) {
            return $this->errorResponse(400, $e->getMessage());
        }

        return $this->messageResponse('Movie added to watchlist');
    }

    public function removeMovie(Request $request, $profileId, $movieId)
    {
        $movie = Movie::findOrFail($movieId);

        $profile = Profile::findOrFail($profileId);
        $profile->watchList()->detach($movieId);

        return $this->messageResponse('Movie removed from watchlist');
    }

    public function addEpisode(Request $request, $profileId, $seriesId, $seasonId, $episodeId)
    {
        $episode = Episode::findOrFail($episodeId);

        $profile = Profile::findOrFail($profileId);
        $profile->watchList()->attach($episodeId);

        return $this->messageResponse('Episode added to watchlist');
    }

    public function removeEpisode(Request $request, $profileId, $seriesId, $seasonId, $episodeId)
    {
        $episode = Episode::findOrFail($episodeId);

        $profile = Profile::findOrFail($profileId);
        $profile->watchList()->detach($episodeId);

        return $this->messageResponse('Episode removed from watchlist');
    }
}
