<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\Episode;
use App\Models\Movie;
use App\Models\Subtitle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SubtitleController extends BaseController
{
    // Store a new subtitle
    public function store(Request $request)
    {
        try {

            $episodeId = $request->route('episodeId');
            $movieId = $request->route('moiveId');

            $validator = Validator::make($request->all(), [
                'language' => 'required|string',
                // 'file' => 'required|mimes:vtt,srt',
                'file_path' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse(400, $validator->errors()->first());
            }

            $subtitle = new Subtitle();
            $subtitle->language = $request->language;
            // $subtitle->file_path = $request->file('file')->store('subtitles');
            $subtitle->subtitle_path = $request->file_path;

            if ($episodeId) {
                // Check if the episode exists
                $episode = Episode::find($episodeId);

                if (!$episode) {
                    return $this->errorResponse(404, 'Episode not found.');
                }

                // Verify that the episode belongs to the correct season and series
                $season_id = $request->route('seasonId');
                $series_id = $request->route('seriesId');

                if ($season_id && $episode->season_id != $season_id) {
                    return $this->errorResponse(400, 'The episode does not match the provided season.');
                }

                if ($series_id && $episode->season->series_id != $series_id) {
                    return $this->errorResponse(400, 'The episode does not match the provided series.');
                }

                $subtitle->episode_id = $episodeId;
                if (!$episode->subtitles()->save($subtitle)) {
                    throw new \Exception('Failed to associate subtitle with episode');
                }
            } elseif ($movieId) {
                // Check if the movie exists
                $movie = Movie::find($movieId);

                if (!$movie) {
                    return $this->errorResponse(404, 'Movie not found.');
                }

                $subtitle->movie_id = $movieId;
                if (!$movie->subtitles()->save($subtitle)) {
                    throw new \Exception('Failed to associate subtitle with movie');
                }
            } else {
                throw new \Exception('Episode ID or Movie ID is required');
            }

            return $this->dataResponse($subtitle, 'Subtitle successfully added.');

        } catch (\Exception $e) {
            return $this->errorResponse(500, 'Failed to store subtitle: ' . $e->getMessage());
        }
    }

    // Retrieve all subtitles for a specific episode or movie
    public function index(Request $request)
    {
        try {
            $episodeId = $request->route('episodeId');
            $movieId = $request->route('movieId');

            $subtitles = []; // Initialize an empty array or collection to store the subtitles

            if ($episodeId) {
                // Check if the episode exists
                $episode = Episode::find($episodeId);

                if (!$episode) {
                    return $this->errorResponse(404, 'Episode not found.');
                }

                // Verify that the episode belongs to the correct season and series
                $season_id = $request->route('seasonId');
                $series_id = $request->route('seriesId');

                if ($season_id && $episode->season_id != $season_id) {
                    return $this->errorResponse(400, 'The episode does not match the provided season.');
                }

                if ($series_id && $episode->season->series_id != $series_id) {
                    return $this->errorResponse(400, 'The episode does not match the provided series.');
                }

                // Retrieve subtitles for the episode
                $subtitles = $episode->subtitles;

            } elseif ($movieId) {
                // Check if the movie exists
                $movie = Movie::find($movieId);

                if (!$movie) {
                    return $this->errorResponse(404, 'Movie not found.');
                }

                // Retrieve subtitles for the movie
                $subtitles = $movie->subtitles;
            } else {
                throw new \Exception('Episode ID or Movie ID is required');
            }

            return $this->dataResponse($subtitles, "Subtitle retrieved successfully");

        } catch (\Exception $e) {
            return $this->errorResponse(400, 'Failed to retrieve subtitles: ' . $e->getMessage());
        }
    }

    // Update an existing subtitle
    public function update(Request $request)
    {
        try {
            $subtitleId = $request->route('subtitleId');

            $validator = Validator::make($request->all(), [
                'language' => 'sometimes|required|string',
                // 'file' => 'sometimes|required|mimes:vtt,srt',
                'file_path' => 'sometimes|string',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse(400, $validator->errors()->first());
            }

            $subtitle = Subtitle::findOrFail($subtitleId);

            if ($request->has('language')) {
                $subtitle->language = $request->language;
            }

            if ($request->hasFile('file_path')) {
                // $subtitle->subtitle_path = $request->file('file_path')->store('subtitles');
                $subtitle->subtitle_path = $request->file_path;
            }

            $subtitle->save();

            return $this->dataResponse($subtitle,'Subtitle successfully updated.');
        } catch (\Exception $e) {
            return $this->errorResponse(404, 'Subtitle not found');
        }
    }

    // Delete a subtitle
    public function destroy(Request $request)
    {
        try {
            $subtitleId = $request->route('subtitleId');
            $subtitle = Subtitle::findOrFail($subtitleId);
            $subtitle->delete();

            return $this->messageResponse('Subtitle successfully deleted.');
        } catch (\Exception $e) {
            return $this->errorResponse(404, 'Subtitle not found');
        }
    }
}
