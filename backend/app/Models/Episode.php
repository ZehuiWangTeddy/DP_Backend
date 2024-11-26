<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Episode extends Model
{
    use HasFactory;

    protected $fillable = [
        'season_id',
        'episode_number',
        'title',
        'quality',
        'duration',
        'available_languages',
        'release_date',
        'viewing_classification',
    ];

    /**
     * Define the relationship to the Season model.
     */
    public function season()
    {
        return $this->belongsTo(Season::class, 'season_id', 'season_id');
    }
}
