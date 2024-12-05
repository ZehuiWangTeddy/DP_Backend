<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Season extends Model
{
    use HasFactory;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'season_id';

    protected $fillable = [
        'series_id',
        'season_number',
        'release_date',
    ];

    /**
     * Define the relationship to the Series model.
     */
    public function series()
    {
        return $this->belongsTo(Series::class, 'series_id', 'series_id');
    }

    /**
     * Define the relationship to the Episode model.
     */
    public function episodes()
    {
        return $this->hasMany(Episode::class, 'season_id', 'season_id');
    }
}
