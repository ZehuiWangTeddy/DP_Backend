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
    public function store(Request $request, $series_id = null, $season_id = null, $episodeId = null, $id = null)
    {
        try {
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
            $subtitle->subtitle_path = $request->file;

            if ($episodeId) {
                $episode = Episode::findOrFail($episodeId);
                $subtitle->episode_id = $episodeId;
                if (!$episode->subtitles()->save($subtitle)) {
                    throw new \Exception('Failed to associate subtitle with episode');
                }
            } elseif ($id) {
                $movie = Movie::findOrFail($id);
                $subtitle->movie_id = $id;
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
    public function index($series_id = null, $season_id = null, $episodeId = null, $id = null)
    {
        try {
            if ($episodeId) {
                $episode = Episode::findOrFail($episodeId);
                $subtitles = $episode->subtitles;
            } elseif ($id) {
                $movie = Movie::findOrFail($id);
                $subtitles = $movie->subtitles;
            } else {
                return $this->messageResponse('Episode or Movie not specified.', 400);
            }
        } catch (\Exception $e) {
            return $this->errorResponse(400, 'Failed to retrieve subtitles: ' . $e->getMessage());
        }
        return $this->dataResponse($subtitles, "Subtitle retrieved successfully");
    }

    // Update an existing subtitle
    public function update(Request $request,$series_id = null, $season_id = null, $episodeId = null, $subtitleId)
    {
        try {
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
    public function destroy($subtitleId)
    {
        try {
            $subtitle = Subtitle::findOrFail($subtitleId);
            $subtitle->delete();

            return $this->messageResponse('Subtitle successfully deleted.');
        } catch (\Exception $e) {
            return $this->errorResponse(404, 'Subtitle not found');
        }
    }
}
