<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\Episode;
use App\Models\Movie;
use App\Models\Profile;
use App\Models\WatchHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WatchHistoryController extends BaseController
{
    public function index($profileId)
    {
        $profile = Profile::find($profileId);
        if (!$profile) {
            return $this->errorResponse(404, 'Profile not found');
        }

        $watchHistory = WatchHistory::where('profile_id', $profileId)->get();

        if ($watchHistory->isEmpty()) {
            return $this->dataResponse('Did not watching movie or episode yet' );
        }

        return $this->dataResponse($watchHistory, 'Watch history retrieved successfully');
    }

    public function startMovie(Request $request, $profileId)
    {
        $validator = Validator::make($request->all(), [
            'movie_id' => 'required|exists:movies,movie_id',
            'resume_to' => [
                'required',
                'date_format:H:i:s',
                function ($attribute, $value, $fail) use ($request) {
                    $movie = Movie::find($request->movie_id);
                    if (!$movie) {
                        return $fail("Movie not found.");
                    }

                    $resumeToInSeconds = strtotime($value) - strtotime('TODAY');
                    $movieDurationInSeconds = strtotime($movie->duration) - strtotime('TODAY');

                    if ($resumeToInSeconds > $movieDurationInSeconds) {
                        $fail("The $attribute cannot exceed the movie's duration of {$movie->duration}.");
                    }
                },
            ],
            'watched_time_stamp' => 'required|date_format:Y-m-d H:i:s',
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

        try {
            // watched time stamp finish cannot early than start
            $existingHistory = WatchHistory::where([
                'profile_id' => $profileId,
                'movie_id' => $validated['movie_id'],
            ])->first();

            if ($existingHistory && $existingHistory->watched_time_stamp >= $validated['watched_time_stamp']) {
                return $this->errorResponse(400, 'watched time stamp finish canot early than start');
            }

            // Create or update viewing history
            $watchHistory = WatchHistory::firstOrNew([
                'profile_id' => $profileId,
                'movie_id' => $validated['movie_id'],
            ]);

            $watchHistory->resume_to = $validated['resume_to'];
            $watchHistory->watched_time_stamp = $validated['watched_time_stamp'];

            $resumeToInSeconds = strtotime($validated['resume_to']) - strtotime('TODAY');
            $movie = Movie::find($validated['movie_id']);
            $movieDurationInSeconds = strtotime($movie->duration) - strtotime('TODAY');
            $watchHistory->viewing_status = $resumeToInSeconds < $movieDurationInSeconds ? 'paused' : 'finished';

            $watchHistory->times_watched = ($watchHistory->times_watched ?? 0) + 1;

            $watchHistory->save();

            // Return success response
            return $this->dataResponse([
                'watchHistory' => $watchHistory->only(['history_id', 'profile_id', 'movie_id', 'resume_to', 'times_watched', 'watched_time_stamp','viewing_status']),
            ], "Movie started watching successfully.");
        } catch (\Exception $e) {

            Log::error($e);

            // Return error response
            return $this->errorResponse(500, 'Failed to watch new movie. Please try again later.');
        }
    }

    public function finishMovie(Request $request, $profileId)
    {
        // Validate input with additional checks
        $validator = Validator::make($request->all(), [
            'movie_id' => 'required|exists:movies',
            'watched_time_stamp' => 'required|date_format:Y-m-d H:i:s',
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
        $watchHistory = WatchHistory::where('profile_id', $profileId)
            ->where('movie_id', $validated['movie_id'])
            ->first();

        if (!$watchHistory) {
            return $this->errorResponse(404, 'The profile has not watched this movie.');
        }

        try {
            // Update the watch history record
            $watchHistory->watched_time_stamp = $validated['watched_time_stamp'];
            $watchHistory->viewing_status = 'finished';
            $watchHistory->save();

            return $this->dataResponse([
                'watchHistory' => $watchHistory->only([
                    'history_id', 'profile_id', 'movie_id', 'resume_to',
                    'times_watched', 'watched_time_stamp', 'viewing_status'
                ]),
            ], "Movie finished successfully.");
        } catch (\Exception $e) {

            Log::error($e);

            return $this->errorResponse(500, 'Failed to finish the movie. Please try again later.');
        }
    }

    public function removeMovie($profileId, $movieId)
    {
        // Check if the profile exists
        if (!Profile::find($profileId)) {
            return $this->errorResponse(404, 'Profile not found.');
        }

        // Attempt to delete the movie from the watchlist
        if (!WatchHistory::where('profile_id', $profileId)->where('movie_id', $movieId)->delete()) {
            return $this->errorResponse(404, 'Movie not found in the watch history.');
        }

        return $this->messageResponse('Movie removed from watch history successfully.', 200);
    }


    public function startEpisode(Request $request, $profileId, $seasonId, $seriesId)
    {
        $episodes = Episode::where('season_id', $seasonId)->get();
        if ($episodes->isEmpty()) {
            return $this->errorResponse(404, 'Episodes not found.');
        }

        $validator = Validator::make($request->all(), [
            'episode_id' => 'required|exists:episodes,episode_id',
            'resume_to' => [
                'required',
                'date_format:H:i:s',
                function ($attribute, $value, $fail) use ($request) {
                    $episode = Episode::find($request->episode_id);
                    if (!$episode) {
                        return $fail("Episode not found.");
                    }

                    $resumeToInSeconds = strtotime($value) - strtotime('TODAY');
                    $episodeDurationInSeconds = strtotime($episode->duration) - strtotime('TODAY');

                    if ($resumeToInSeconds > $episodeDurationInSeconds) {
                        $fail("The $attribute cannot exceed the episode's duration of {$episode->duration}.");
                    }
                },
            ],
            'watched_time_stamp' => 'required|date_format:Y-m-d H:i:s',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(400, $validator->errors()->first());
        }

        $validated = $validator->safe();

        $profile = Profile::find($profileId);
        if (!$profile) {
            return $this->errorResponse(404, 'Profile not found.');
        }

        try {
            // Check if viewing history exists
            $existingHistory = WatchHistory::where([
                'profile_id' => $profileId,
                'episode_id' => $validated['episode_id'],
            ])->first();

            if ($existingHistory && $existingHistory->watched_time_stamp >= $validated['watched_time_stamp']) {
                return $this->errorResponse(400, 'The new viewing time must be after the last viewing time');
            }

            // Create or update viewing history
            $watchHistory = WatchHistory::firstOrNew([
                'profile_id' => $profileId,
                'episode_id' => $validated['episode_id'],
            ]);

            $watchHistory->resume_to = $validated['resume_to'];
            $watchHistory->watched_time_stamp = $validated['watched_time_stamp'];

            $resumeToInSeconds = strtotime($validated['resume_to']) - strtotime('TODAY');
            $episode = Episode::find($validated['episode_id']);
            $episodeDurationInSeconds = strtotime($episode->duration) - strtotime('TODAY');

            $watchHistory->viewing_status = $resumeToInSeconds < $episodeDurationInSeconds ? 'paused' : 'finished';

            $watchHistory->times_watched = ($watchHistory->times_watched ?? 0) + 1;

            $watchHistory->save();

            return $this->dataResponse([
                'watchHistory' => $watchHistory->only(['history_id', 'profile_id', 'episode_id', 'resume_to', 'times_watched', 'watched_time_stamp', 'viewing_status']),
            ], "Episode started watching successfully.");
        } catch (\Exception $e) {

            Log::error("Error in startEpisode: {$e->getMessage()}");

            return $this->errorResponse(500, 'Failed to watch new episode. Please try again later.');
        }
    }

    public function finishEpisode(Request $request, $profileId, $seasonId, $seriesId)
    {
        $episodes = Episode::where('season_id', $seasonId)->get();
        if ($episodes->isEmpty()) {
            return $this->errorResponse(404, 'Episodes not found.');
        }

        // Validate input with additional checks
        $validator = Validator::make($request->all(), [
            'episode_id' => 'required|exists:episodes',
            'watched_time_stamp' => 'required|date_format:Y-m-d H:i:s',
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
        $watchHistory = WatchHistory::where('profile_id', $profileId)
            ->where('episode_id', $validated['episode_id'])
            ->first();

        if (!$watchHistory) {
            return $this->errorResponse(404, 'The profile has not watched this episode.');
        }

        try {
            // Update the watch history record
            $watchHistory->watched_time_stamp = $validated['watched_time_stamp'];
            $watchHistory->viewing_status = 'finished';
            $watchHistory->save();

            return $this->dataResponse([
                'watchHistory' => $watchHistory->only([
                    'history_id', 'profile_id', 'episode_id', 'resume_to',
                    'times_watched', 'watched_time_stamp', 'viewing_status'
                ]),
            ], "Episode finished successfully.");
        } catch (\Exception $e) {

            Log::error($e);

            return $this->errorResponse(500, 'Failed to finish the episode. Please try again later.');
        }
    }

    public function removeEpisode($profileId, $seasonId, $seriesId, $episodeId)
    {
        // Check if the profile exists
        if (!Profile::find($profileId)) {
            return $this->errorResponse(404, 'Profile not found.');
        }

        // Attempt to delete the episode from the watchlist
        if (!WatchHistory::where('profile_id', $profileId)->where('episode_id', $episodeId)->delete()) {
            return $this->errorResponse(404, 'Episode not found in the watch history.');
        }

        return $this->messageResponse('Episode removed from watch history successfully.', 200);
    }
}
