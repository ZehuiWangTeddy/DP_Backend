<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\Episode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EpisodeController extends BaseController
{
    private function validateEpisode(Request $request, $isUpdate = false)
    {
        $rules = [
            'season_id' => $isUpdate ? 'sometimes|exists:seasons,season_id' : 'required|exists:seasons,season_id',
            'episode_number' => $isUpdate ? 'sometimes|integer' : 'required|integer',
            'title' => $isUpdate ? 'sometimes|string|max:255' : 'required|string|max:255',
            'quality' => $isUpdate ? 'sometimes' : 'in:HD,SD,UHD',
            'duration' => $isUpdate ? 'sometimes|regex:/^\d{2}:\d{2}:\d{2}$/' : 'required|regex:/^\d{2}:\d{2}:\d{2}$/',
            'available_languages' => $isUpdate ? 'sometimes|array' : 'required|array',
            'release_date' => $isUpdate ? 'sometimes|date' : 'required|date',
            'viewing_classification' => $isUpdate ? 'sometimes|string' : 'required|string',
            'file' => $isUpdate ? 'sometimes|file|mimes:mp4|max:20480' : 'nullable|file|mimes:mp4|max:20480',
        ];

        return $request->validate($rules);
    }

    private function encodeFields($data)
    {
        foreach (['available_languages'] as $field) {
            if (isset($data[$field])) {
                $data[$field] = json_encode($data[$field]);
            }
        }
        return $data;
    }

    public function index(Request $request, $seriesId, $seasonId)
    {
        try {
            $episodes = Episode::where('season_id', $seasonId)->get();
            return $this->dataResponse($episodes, 'Episodes retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse(400, 'Failed to retrieve episodes: ' . $e->getMessage());
        }

    }

    public function store(Request $request)
    {
        try {
            $validated = $this->validateEpisode($request);
            $validated = $this->encodeFields($validated);

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $safeName = time() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "", $file->getClientOriginalName());
                $path = $file->storeAs('media/episodes', $safeName, 'public');
                $validated['file_path'] = $path;
            }

            $episode = Episode::create($validated);

            return $this->dataResponse([
                'episode' => $episode,
                'url' => isset($path) ? Storage::url($path) : null
            ], 'Episode created successfully');
        } catch (\Exception $e) {
            return $this->errorResponse(500, 'Failed to create episode: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $episode = Episode::findOrFail($id);
        return $this->dataResponse($episode, 'Episode retrieved successfully');
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
            $episode = Episode::findOrFail($id);

            if ($episode->file_path && Storage::exists($episode->file_path)) {
                Storage::delete($episode->file_path);
            }

            $episode->delete();
            return $this->messageResponse('Episode deleted successfully', 200);
        } catch (\Exception $e) {
            return $this->errorResponse(500, 'Failed to delete episode: ' . $e->getMessage());
        }
    }
}
