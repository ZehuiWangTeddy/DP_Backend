<?php

namespace App\Http\Controllers;

use App\Models\Season;
use App\Models\Series;
use Illuminate\Http\Request;

class SeasonController extends Controller
{
    /**
     * Display a listing of the seasons for a series.
     */
    public function index($seriesId)
    {
        $series = Series::findOrFail($seriesId);
        $seasons = $series->seasons;
        return response()->json($seasons, 200);
    }

    /**
     * Store a newly created season.
     */
    public function store(Request $request, $seriesId)
    {
        $series = Series::findOrFail($seriesId);
        
        $validated = $request->validate([
            'season_number' => 'required|integer|min:1',
            'release_date' => 'required|date',
        ]);

        $validated['series_id'] = $seriesId;
        $season = Season::create($validated);

        return response()->json($season, 201);
    }

    /**
     * Update the specified season.
     */
    public function update(Request $request, $seriesId)
    {
        $validated = $request->validate([
            'season_number' => 'sometimes|integer|min:1',
            'release_date' => 'sometimes|date',
        ]);

        $season = Season::where('series_id', $seriesId)->findOrFail($request->season_id);
        $season->update($validated);

        return response()->json($season, 200);
    }

    /**
     * Remove the specified season.
     */
    public function destroy($seriesId)
    {
        $season = Season::where('series_id', $seriesId)->findOrFail(request('season_id'));
        $season->delete();

        return response()->json(['message' => 'Season deleted successfully'], 200);
    }
} 