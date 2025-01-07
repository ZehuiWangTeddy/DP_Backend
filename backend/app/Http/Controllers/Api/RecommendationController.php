<?php

namespace App\Http\Controllers;

use App\Models\WatchHistory;
use App\Models\Preference;
use App\Models\Movie;
use App\Models\Series;
use Illuminate\Http\Request;

class RecommendationController extends BaseController 
{
    public function index($id)
    {
        $preferences = Preference::where('profile_id', $id)->get();

        $preferredGenres = $preferences->pluck('genre')->toArray();

        $watchHistory = WatchHistory::where('profile_id', $id)
            ->get(['movie_id', 'series_id']);

        $recommendedMovies = Movie::whereIn('genre', $preferredGenres)
            ->whereNotIn('movie_id', $watchHistory->pluck('movie_id'))
            ->get();

        $recommendedSeries = Series::whereIn('genre', $preferredGenres)
            ->whereNotIn('series_id', $watchHistory->pluck('series_id'))
            ->get();

        return $this->dataResponse([
            'movies' => $recommendedMovies,
            'series' => $recommendedSeries
        ], 'Recommendations retrieved successfully');
    }
}
