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
            'season_id' => $isUpdate ? 'sometimes|exists:seasons,season_id' : 'required|exists:seasons,season_id',
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

    public function store(Request $request)
    {
        try {
            $validated = $this->validateEpisode($request);
            $validated = $this->encodeFields($validated);

            $episode = Episode::create($validated);

            return $this->dataResponse([
                'episode' => $episode,
            ], 'Episode created successfully');
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

    public function update(Request $request, $id)
    {
        try {
            $episode = Episode::findOrFail($id);
            $validated = $this->validateEpisode($request, true);
            $validated = $this->encodeFields($validated);

            if ($request->hasFile('file')) {
                if ($episode->file_path && Storage::exists($episode->file_path)) {
                    Storage::delete($episode->file_path);
                }

                $file = $request->file('file');
                $safeName = time() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "", $file->getClientOriginalName());
                $path = $file->storeAs('media/episodes', $safeName, 'public');
                $validated['file_path'] = $path;
            }

            $episode->update($validated);

            return $this->dataResponse([
                'episode' => $episode,
                'url' => isset($path) ? Storage::url($path) : null
            ], 'Episode updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse(500, 'Failed to update episode: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $episode = Episode::find($id);
            if (!$episode) {
                return $this->errorResponse(404, 'Episode not found');
            }
            $episode->delete();
            return $this->messageResponse('Episode deleted successfully', 200);
        } catch (\Exception $e) {
            return $this->errorResponse(500, 'Failed to delete episode: ' . $e->getMessage());
        }
    }
}
