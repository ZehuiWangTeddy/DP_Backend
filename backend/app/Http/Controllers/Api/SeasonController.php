<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\Season;
use App\Models\Series;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SeasonController extends BaseController
{
    /**
     * Display a listing of the seasons for a series.
     */
    public function index($seriesId)
    {
        try {
            $series = Series::findOrFail($seriesId);

            $seasons = $series->seasons ?? [];

            return $this->dataResponse($seasons, 'Seasons retrieved successfully');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse(404, 'Series not found');
        } catch (\Exception $e) {
            return $this->errorResponse(500, 'An unexpected error occurred: ' . $e->getMessage());
        }
    }


    /**
     * Store a newly created season.
     */
    public function store(Request $request, $seriesId)
    {
        try {
            $validated = $request->validate([
                'season_number' => 'required|integer|min:1',
                'release_date' => 'required|date',
            ]);

            $series = Series::find($seriesId);
            if (!$series) {
                return $this->errorResponse(404, 'Series not found');
            }

            $validated['series_id'] = $seriesId;

            $season = Season::create($validated);

            return $this->dataResponse($season, 'Season created successfully');
        } catch (ValidationException $e) {
            return $this->errorResponse(422, 'Validation error: ' . $e->getMessage());
        } catch (\Exception $e) {
            return $this->errorResponse(500, 'An unexpected error occurred: ' . $e->getMessage());
        }
    }


    /**
     * Update the specified season.
     */
    public function update(Request $request, $seriesId, $seasonId)
    {
        try {
            $validated = $request->validate([
                'season_number' => 'sometimes|integer|min:1',
                'release_date' => 'sometimes|date',
            ]);

            $season = Season::where('series_id', $seriesId)
                ->where('season_id', $seasonId)
                ->firstOrFail();

            $season->update($validated);

            return $this->dataResponse($season, 'Season updated successfully');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse(404, 'Season not found');
        } catch (ValidationException $e) {
            return $this->errorResponse(422, 'Validation error: ' . $e->getMessage());
        } catch (\Exception $e) {
            return $this->errorResponse(500, 'An unexpected error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified season.
     */
    public function destroy($seriesId, $seasonId)
    {
        try {
            $season = Season::where('series_id', $seriesId)
                ->where('season_id', $seasonId)
                ->firstOrFail();

            $season->delete();

            return $this->messageResponse('Season deleted successfully');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse(404, 'Season not found');
        } catch (\Exception $e) {
            return $this->errorResponse(500, 'An unexpected error occurred: ' . $e->getMessage());
        }
    }

}
