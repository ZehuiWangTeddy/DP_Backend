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
        $watchHistory = WatchHistory::where('profile_id', $profileId)->get();

        if (!$watchHistory) {
            return $this->errorResponse('Watch history not found', 404);
        }

        return $this->dataResponse($watchHistory, 'Preferences retrieved successfully');
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

        // Start database transaction
        DB::beginTransaction();

        try {
            // Create the watch history record in the database
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

            if (!$watchHistory->exists || $watchHistory->viewing_status === 'finished') {
                $watchHistory->times_watched = ($watchHistory->times_watched ?? 0) + 1;
            }

            $watchHistory->save();

            // Commit the transaction
            DB::commit();

            // Return success response
            return $this->dataResponse([
                'watchHistory' => $watchHistory->only(['watchHistory_id', 'profile_id', 'movie_id', 'resume_to', 'times_watched', 'watched_time_stamp','viewing_status']),
            ], "Movie started watching successfully.");
        } catch (\Exception $e) {
            // Rollback the transaction if an error occurs
            DB::rollBack();

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

        // Start database transaction
        DB::beginTransaction();

        try {
            // Update the watch history record
            $watchHistory->watched_time_stamp = $validated['watched_time_stamp'];
            $watchHistory->viewing_status = 'finished';
            $watchHistory->save();

            // Commit the transaction
            DB::commit();

            return $this->dataResponse([
                'watchHistory' => $watchHistory->only([
                    'watchHistory_id', 'profile_id', 'movie_id', 'resume_to',
                    'times_watched', 'watched_time_stamp', 'viewing_status'
                ]),
            ], "Movie finished successfully.");
        } catch (\Exception $e) {
            // Rollback the transaction if an error occurs
            DB::rollBack();

            Log::error($e);

            return $this->errorResponse(500, 'Failed to finish the movie. Please try again later.');
        }
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
