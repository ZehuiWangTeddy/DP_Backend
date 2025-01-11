<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\Movie;
use App\Models\Preference;
use App\Models\Series;
use App\Models\Episode;
use App\Models\WatchHistory;

class RecommendationController extends BaseController
{
    public function index($id)
    {
        // Retrieve the profile's preferences
        $preferences = Preference::where('profile_id', $id)->get();

        // Extract the preferred genres
        $preferredGenres = $preferences->pluck('genre')->toArray();

        // Retrieve the profile's watch history
        $watchHistory = WatchHistory::where('profile_id', $id)->get(['movie_id', 'episode_id']);

        // Extract watched series IDs via episode -> season -> series
        $watchedSeriesIds = Episode::whereIn('episode_id', $watchHistory->pluck('episode_id'))
            ->join('seasons', 'episodes.season_id', '=', 'seasons.season_id')
            ->pluck('seasons.series_id')
            ->toArray();

        if (empty($preferredGenres)) {
            // If no preferences are found, use genres from watched movies and series
            $watchedMovieGenres = Movie::whereIn('movie_id', $watchHistory->pluck('movie_id'))
                ->pluck('genre')
                ->toArray();

            $watchedSeriesGenres = Series::whereIn('series_id', $watchedSeriesIds)
                ->pluck('genre')
                ->toArray();

            $preferredGenres = array_unique(array_merge($watchedMovieGenres, $watchedSeriesGenres));

            if (empty($preferredGenres)) {
                // If no genres are found in the watch history, recommend the most popular movies/series
                $recommendedMovies = Movie::withCount('watchHistories')
                    ->orderByDesc('watch_histories_count')
                    ->take(5)
                    ->get();

                $recommendedSeries = Series::withCount(['episodes.watchHistories as watch_histories_count'])
                    ->orderByDesc('watch_histories_count')
                    ->take(5)
                    ->get();

                return $this->dataResponse([
                    'movies' => $recommendedMovies,
                    'series' => $recommendedSeries,
                ], 'Top popular recommendations retrieved successfully');
            }
        }

        // Recommend movies based on preferred genres, excluding watched movies
        $recommendedMovies = Movie::whereIn('genre', $preferredGenres)
            ->whereNotIn('movie_id', $watchHistory->pluck('movie_id'))
            ->get();

        // Recommend series based on preferred genres, excluding watched series
        $recommendedSeries = Series::whereIn('genre', $preferredGenres)
            ->whereNotIn('series_id', $watchedSeriesIds)
            ->get();

        return $this->dataResponse([
            'movies' => $recommendedMovies,
            'series' => $recommendedSeries,
        ], 'Recommendations retrieved successfully');
    }
}
