<?php

namespace App\Http\Controllers;

use App\Models\WatchHistory;
use App\Models\Preference;
use App\Models\Movie;
use App\Models\Series;
use Illuminate\Http\Request;

class RecommendationController extends Controller
{

    public function index($id)
    {
        // Fetch user's preferences
        $preferences = Preference::where('profile_id', $id)->get();

        // Extract genres from preferences
        $preferredGenres = $preferences->pluck('genre')->toArray();

        // Fetch watch history for user
        $watchHistory = WatchHistory::where('profile_id', $id)
            ->get(['movie_id', 'series_id']);

        // Fetch movies and series the user hasn't watched yet, but match their preferred genres
        $recommendedMovies = Movie::whereIn('genre', $preferredGenres)
            ->whereNotIn('movie_id', $watchHistory->pluck('movie_id'))
            ->get();

        $recommendedSeries = Series::whereIn('genre', $preferredGenres)
            ->whereNotIn('series_id', $watchHistory->pluck('series_id'))
            ->get();

        return response()->json([
            'movies' => $recommendedMovies,
            'series' => $recommendedSeries,
            'message' => 'Recommendations retrieved successfully'
        ], 200);
    }

}