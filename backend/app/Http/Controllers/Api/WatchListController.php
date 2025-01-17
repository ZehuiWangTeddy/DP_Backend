<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\Episode;
use App\Models\Movie;
use App\Models\Profile;
use App\Models\Watchlist;
use App\Models\WatchHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class WatchListController extends BaseController
{
    public function index($profileId)
    {
        $profile = Profile::find($profileId);
        if (!$profile) {
            return $this->errorResponse(404, 'Profile not found');
        }

        $watchList = Watchlist::where('profile_id', $profileId)
            ->where('viewing_status', '!=', 'finished')
            ->get();

        if ($watchList->isEmpty()) {
            return $this->dataResponse( 'Watch list is finished or you did not add anything to watch list');
        }

        return $this->dataResponse($watchList, 'Watch list retrieved successfully');
    }

    public function addMovie(Request $request, $profileId)
    {
        $validator = Validator::make($request->all(), [
            'movie_id' => 'required|exists:movies,movie_id',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(400, $validator->errors()->first());
        }

        $validated = $validator->safe();

        $profile = Profile::find($profileId);
        if (!$profile) {
            return $this->errorResponse(404, 'Profile not found.');
        }

        $existingWatchList = Watchlist::where('profile_id', $profileId)
            ->where('movie_id', $validated['movie_id'])
            ->first();

        if ($existingWatchList) {
            return $this->errorResponse(400, 'Movie is already in the watchlist.');
        }

        try {
            $watchHistory = WatchHistory::where('profile_id', $profileId)
                ->where('movie_id', $validated['movie_id'])
                ->first();

            $viewingStatus = $watchHistory ? 'paused' : 'to_watch';

            $watchList = Watchlist::create([
                'profile_id' => $profileId,
                'movie_id' => $validated['movie_id'],
                'viewing_status' => $viewingStatus,
            ]);

            return $this->dataResponse([
                'watchlist' => $watchList->only(['watchlist_id', 'profile_id', 'movie_id', 'viewing_status']),
            ], "Movie added to watchlist successfully.");
        } catch (\Exception $e) {

            Log::error("Error adding movie to watchlist: {$e->getMessage()}");

            return $this->errorResponse(500, 'Failed to add movie to watchlist. Please try again later.');
        }
    }

    public function finishMovie(Request $request, $profileId)
    {
        // Validate input with additional checks
        $validator = Validator::make($request->all(), [
            'movie_id' => 'required|exists:movies',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(400, $validator->errors()->first());
        }

        $validated = $validator->safe();

        // Check if the profile exists
        $profile = Profile::find($profileId);
        if (!$profile) {
            return $this->errorResponse(404, 'Profile not found.');
        }

        // Check if the profile has watched this movie
        $watchList = Watchlist::where('profile_id', $profileId)
            ->where('movie_id', $validated['movie_id'])
            ->first();

        if (!$watchList) {
            return $this->errorResponse(404, 'The profile has not liked this movie.');
        }

        try {
            // Update the watch history record
            $watchList->viewing_status = 'finished';
            $watchList->save();

            return $this->dataResponse([
                'watchList' => $watchList->only([
                    'watchlist_id', 'profile_id', 'movie_id', 'viewing_status'
                ]),
            ], "Movie hided successfully.");
        } catch (\Exception $e) {

            Log::error($e);

            return $this->errorResponse(500, 'Failed to remove the movie. Please try again later.');
        }
    }

    public function removeMovie($profileId, $movieId)
    {
        // Check if the profile exists
        if (!Profile::find($profileId)) {
            return $this->errorResponse(404, 'Profile not found.');
        }

        // Attempt to delete the movie from the watchlist
        if (!Watchlist::where('profile_id', $profileId)->where('movie_id', $movieId)->delete()) {
            return $this->errorResponse(404, 'Movie not found in the watchlist.');
        }

        return $this->messageResponse('Movie removed from watchlist successfully.', 200);
    }


    public function addEpisode(Request $request, $profileId, $seasonId, $seriesId)
    {
        $episodes = Episode::where('season_id', $seasonId)->get();
        if ($episodes ->isEmpty()) {
            return $this->errorResponse(404, 'Episodes not found.');
        }

        $validator = Validator::make($request->all(), [
            'episode_id' => 'required|required|exists:episodes,episode_id',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(400, $validator->errors()->first());
        }

        $validated = $validator->safe();

        $profile = Profile::find($profileId);
        if (!$profile) {
            return $this->errorResponse(404, 'Profile not found.');
        }

        $existingWatchList = Watchlist::where('profile_id', $profileId)
            ->where('episode_id', $validated['episode_id'])
            ->first();

        if ($existingWatchList) {
            return $this->errorResponse(400, 'Episode is already in the watchlist.');
        }

        try {
            $watchHistory = WatchHistory::where('profile_id', $profileId)
                ->where('episode_id', $validated['episode_id'])
                ->first();

            $viewingStatus = $watchHistory ? 'paused' : 'to_watch';

            $watchList = Watchlist::create([
                'profile_id' => $profileId,
                'episode_id' => $validated['episode_id'],
                'viewing_status' => $viewingStatus,
            ]);

            return $this->dataResponse([
                'watchlist' => $watchList->only(['watchlist_id', 'profile_id', 'episode_id', 'viewing_status']),
            ], "Episode added to watchlist successfully.");
        } catch (\Exception $e) {

            Log::error("Error adding episode to watchlist: {$e->getMessage()}");

            return $this->errorResponse(500, 'Failed to add episode to watchlist. Please try again later.');
        }
    }

    public function finishEpisode(Request $request,  $profileId, $seasonId, $seriesId)
    {
        $episodes = Episode::where('season_id', $seasonId)->get();
        if ($episodes->isEmpty()) {
            return $this->errorResponse(404, 'Episodes not found.');
        }

        // Validate input with additional checks
        $validator = Validator::make($request->all(), [
            'episode_id' => 'required|exists:episodes',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(400, $validator->errors()->first());
        }

        $validated = $validator->safe();

        // Check if the profile exists
        $profile = Profile::find($profileId);
        if (!$profile) {
            return $this->errorResponse(404, 'Profile not found.');
        }

        // Check if the profile has watched this movie
        $watchList = Watchlist::where('profile_id', $profileId)
            ->where('episode_id', $validated['episode_id'])
            ->first();

        if (!$watchList) {
            return $this->errorResponse(404, 'The profile has not liked this episode.');
        }

        try {
            // Update the watch history record
            $watchList->viewing_status = 'finished';
            $watchList->save();

            return $this->dataResponse([
                'watchList' => $watchList->only([
                    'watchlist_id', 'profile_id', 'episode_id', 'viewing_status'
                ]),
            ], "Episode hided successfully.");
        } catch (\Exception $e) {

            Log::error($e);

            return $this->errorResponse(500, 'Failed to hide the episode. Please try again later.');
        }
    }

    public function removeEpisode($profileId, $seasonId, $seriesId, $episodeId)
    {
        // Check if the profile exists
        if (!Profile::find($profileId)) {
            return $this->errorResponse(404, 'Profile not found.');
        }

        // Attempt to delete the episode from the watchlist
        if (!Watchlist::where('profile_id', $profileId)->where('episode_id', $episodeId)->delete()) {
            return $this->errorResponse(404, 'Episode not found in the watchlist.');
        }

        return $this->messageResponse('Episode removed from watchlist successfully.', 200);
    }
}
