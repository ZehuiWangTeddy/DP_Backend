<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\Season;
use App\Models\Series;
use Illuminate\Http\Request;

class SeasonController extends BaseController
{
    /**
     * Display a listing of the seasons for a series.
     */
    public function index($seriesId)
    {
        try {
            $series = Series::findOrFail($seriesId);
            $seasons = $series->seasons;

            return $this->dataResponse($seasons, 'Seasons retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse(400, 'Failed to retrieve seasons: no series found');
        }
    }

    /**
     * Store a newly created season.
     */
    public function store(Request $request, $seriesId)
    {
        $validated = $request->validate([
            'season_number' => 'required|integer|min:1',
            'release_date' => 'required|date',
        ]);

        $validated['series_id'] = $seriesId;
        $season = Season::create($validated);

        return $this->dataResponse($season, 'Season created successfully');
    }

    /**
     * Update the specified season.
     */
    public function update(Request $request, $seriesId, $seasonId)
    {
        $validated = $request->validate([
            'season_number' => 'sometimes|integer|min:1',
            'release_date' => 'sometimes|date',
        ]);

        $season = Season::where('series_id', $seriesId)
            ->where('season_id', $seasonId)
            ->firstOrFail();
        $season->update($validated);

        return $this->dataResponse($season);
    }

    /**
     * Remove the specified season.
     */
    public function destroy($seriesId, $seasonId)
    {
        $season = Season::where('series_id', $seriesId)
            ->where('season_id', $seasonId)
            ->firstOrFail();
        $season->delete();

        return $this->messageResponse('Season deleted successfully');
    }
}
