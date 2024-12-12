<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\Episode;
use App\Models\Profile;
use Illuminate\Http\Request;

class WatchListController extends Controller
{
    public function addMovie(Request $request, $profileId, $movieId): \Illuminate\Http\JsonResponse
    {
        $movie = Movie::findOrFail($movieId);

        $profile = Profile::findOrFail($profileId);
        $profile->watchList()->attach($movieId);

        return response()->json(['message' => 'Movie added to watchlist'], 200);
    }

    public function removeMovie(Request $request, $profileId, $movieId): \Illuminate\Http\JsonResponse
    {
        $movie = Movie::findOrFail($movieId);

        $profile = Profile::findOrFail($profileId);
        $profile->watchList()->detach($movieId);

        return response()->json(['message' => 'Movie removed from watchlist'], 200);
    }

    public function addEpisode(Request $request, $profileId, $seriesId, $seasonId, $episodeId): \Illuminate\Http\JsonResponse
    {
        $episode = Episode::findOrFail($episodeId);

        $profile = Profile::findOrFail($profileId);
        $profile->watchList()->attach($episodeId);

        return response()->json(['message' => 'Episode added to watchlist'], 200);
    }

    public function removeEpisode(Request $request, $profileId, $seriesId, $seasonId, $episodeId): \Illuminate\Http\JsonResponse
    {
        $episode = Episode::findOrFail($episodeId);

        $profile = Profile::findOrFail($profileId);
        $profile->watchList()->detach($episodeId);

        return response()->json(['message' => 'Episode removed from watchlist'], 200);
    }
}
