<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\Movie;
use App\Models\Preference;
use App\Models\Series;
use App\Models\Episode;
use App\Models\WatchHistory;
use Illuminate\Support\Facades\DB;

class RecommendationController extends BaseController
{
    public function index($id)
    {
        $preferences = Preference::where('profile_id', $id)->first();

        $watchHistory = WatchHistory::where('profile_id', $id)
            ->where('viewing_status', 'finished')
            ->select('movie_id', 'episode_id', DB::raw('COUNT(*) as watch_count'))
            ->groupBy('movie_id', 'episode_id')
            ->get();

        $watchedMovieIds = $watchHistory->pluck('movie_id')->filter()->unique();
        $watchedEpisodeIds = $watchHistory->pluck('episode_id')->filter()->unique();

        $watchedSeriesIds = Episode::whereIn('episode_id', $watchedEpisodeIds)
            ->join('seasons', 'episodes.season_id', '=', 'seasons.season_id')
            ->pluck('seasons.series_id')
            ->unique()
            ->toArray();

        $movieQuery = Movie::query();
        $seriesQuery = Series::query();

        if ($preferences) {
            // Filter by user preferences
            $preferredGenres = json_decode($preferences->genre);
            if (!empty($preferredGenres)) {
                $movieQuery->whereJsonContains('genre', $preferredGenres);
                $seriesQuery->where(function($query) use ($preferredGenres) {
                    foreach ($preferredGenres as $genre) {
                        $query->orWhere('genre', 'like', "%$genre%");
                    }
                });
            }

            // Filter by age restriction
            $minAge = $preferences->minimum_age;
            $movieQuery->where('age_restriction', '>=', $minAge);
            $seriesQuery->where('age_restriction', '>=', $minAge);

            // Filter by content preference
            if ($preferences->content_preference !== 'both') {
                if ($preferences->content_preference === 'movies') {
                    $seriesQuery->whereNull('series_id');
                } elseif ($preferences->content_preference === 'series') {
                    $movieQuery->whereNull('movie_id');
                }
            }
        }

        // Exclude watched content
        $movieQuery->whereNotIn('movie_id', $watchedMovieIds);
        $seriesQuery->whereNotIn('series_id', $watchedSeriesIds);

        // Get recommendation results
        $recommendedMovies = $movieQuery->take(10)->get();
        $recommendedSeries = $seriesQuery->take(10)->get();

        // If not enough recommendations, add popular content
        if ($recommendedMovies->count() < 5 || $recommendedSeries->count() < 5) {
            $popularMovies = Movie::whereNotIn('movie_id', $watchedMovieIds)
                ->join('watchhistories', 'movies.movie_id', '=', 'watchhistories.movie_id')
                ->select('movies.*', DB::raw('COUNT(watchhistories.movie_id) as watch_count'))
                ->groupBy('movies.movie_id')
                ->orderByDesc('watch_count')
                ->take(5)
                ->get();

            $popularSeries = Series::whereNotIn('series_id', $watchedSeriesIds)
                ->join('seasons', 'series.series_id', '=', 'seasons.series_id')
                ->join('episodes', 'seasons.season_id', '=', 'episodes.season_id')
                ->join('watchhistories', 'episodes.episode_id', '=', 'watchhistories.episode_id')
                ->select('series.*', DB::raw('COUNT(DISTINCT watchhistories.profile_id) as viewer_count'))
                ->groupBy('series.series_id')
                ->orderByDesc('viewer_count')
                ->take(5)
                ->get();

            $recommendedMovies = $recommendedMovies->merge($popularMovies)->unique('movie_id')->take(10);
            $recommendedSeries = $recommendedSeries->merge($popularSeries)->unique('series_id')->take(10);
        }

        return $this->dataResponse([
            'movies' => $recommendedMovies,
            'series' => $recommendedSeries,
        ], 'Recommendations retrieved successfully');
    }
}
