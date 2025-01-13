<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\Episode;
use App\Models\Season;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class EpisodeController extends BaseController
{
    private function validateEpisode(Request $request, $isUpdate = false)
    {
        $rules = [
            // 'season_id' => $isUpdate ? 'sometimes|exists:seasons,season_id' : 'required|exists:seasons,season_id',
            'episode_number' => $isUpdate ? 'sometimes|integer' : 'required|integer',
            'title' => $isUpdate ? 'sometimes|string|max:255' : 'required|string|max:255',
            'quality' => $isUpdate ? 'sometimes|array|in:SD,HD,UHD' : 'required|array|in:SD,HD,UHD',
            'duration' => $isUpdate ? 'sometimes|regex:/^\d{2}:\d{2}:\d{2}$/' : 'required|regex:/^\d{2}:\d{2}:\d{2}$/',
            'available_languages' => $isUpdate ? 'sometimes|array' : 'required|array',
            'release_date' => $isUpdate ? 'sometimes|date' : 'required|date',
            'viewing_classification' => $isUpdate ? 'sometimes|string|in:18+,For Kids,Includes Violence,Includes Sex,Family Friendly,Educational,Sci-Fi Themes,Fantasy Elements' : 'required|string|in:18+,For Kids,Includes Violence,Includes Sex,Family Friendly,Educational,Sci-Fi Themes,Fantasy Elements',
        ];

        return $request->validate($rules);
    }

    private function encodeFields($data)
    {
        foreach (['quality', 'genre', 'viewing_classification', 'available_languages'] as $field) {
            if (isset($data[$field])) {
                $data[$field] = json_encode($data[$field]);
            }
        }
        return $data;
    }

    public function index(Request $request, $seriesId, $seasonId)
    {
        try {
            $season = Season::where('series_id', $seriesId)->findOrFail($seasonId);

            $perPage = $request->query('per_page', 10);
            $episodes = Episode::where('season_id', $seasonId)->paginate($perPage);

            return $this->paginationResponse($episodes, 'Episodes retrieved successfully');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse(404, 'Season not found or does not belong to the specified series');
        } catch (\Exception $e) {
            return $this->errorResponse(500, 'Failed to retrieve episodes: ' . $e->getMessage());
        }
    }

    public function store(Request $request, $seriesId, $seasonId)
    {
        try {
            // Ensure the season belongs to the series
            $season = Season::where('series_id', $seriesId)
                ->where('season_id', $seasonId)
                ->firstOrFail();

            $validated = $this->validateEpisode($request);

            $validated['season_id'] = $season->season_id;

            $validated = $this->encodeFields($validated);

            // Create the episode
            $episode = Episode::create($validated);

            return $this->dataResponse([
                'episode' => $episode,
            ], 'Episode created successfully');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse(404, 'Season not found or does not belong to the specified series');
        } catch (ValidationException $e) {
            return $this->errorResponse(422, 'Validation error: ' . $e->getMessage());
        } catch (\Exception $e) {
            return $this->errorResponse(500, 'An unexpected error occurred: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        try {
            $episode = Episode::findOrFail($id);
            return $this->dataResponse($episode, 'Episode retrieved successfully');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse(404, 'Season not found or does not belong to the specified series');
        } catch (\Exception $e) {
            return $this->errorResponse(500, 'Failed to retrieve episodes: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $series_id, $season_id, $episode_id)
    {
        try {
            $episode = Episode::where('episode_id', $episode_id)
                ->whereHas('season', function ($query) use ($series_id, $season_id) {
                    $query->where('season_id', $season_id)
                        ->where('series_id', $series_id);
                })
                ->first();

            if (!$episode) {
                return $this->errorResponse(404, 'Episode not found or does not match the series and season');
            }

            $validated = $this->validateEpisode($request, true);
            $validated = $this->encodeFields($validated);

            $episode->update($validated);

            return $this->dataResponse([
                'episode' => $episode,
            ], 'Episode updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse(500, 'Failed to update episode: ' . $e->getMessage());
        }
    }

    public function destroy(Request $request, $series_id, $season_id, $episode_id)
    {
        try {
            // Ensure the episode belongs to the correct series and season
            $episode = Episode::where('episode_id', $episode_id)
                ->whereHas('season', function ($query) use ($series_id, $season_id) {
                    $query->where('season_id', $season_id)
                        ->where('series_id', $series_id);
                })
                ->first();

            if (!$episode) {
                return $this->errorResponse(404, 'Episode not found or does not match the series and season');
            }

            $episode->delete();

            return $this->messageResponse('Episode deleted successfully', 200);
        } catch (\Exception $e) {
            return $this->errorResponse(500, 'Failed to delete episode: ' . $e->getMessage());
        }
    }
}
