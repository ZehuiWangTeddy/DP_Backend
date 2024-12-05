<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Movie extends Model
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
    protected $primaryKey = 'movie_id';

    protected $fillable = [
        'title',
        'duration',
        'release_date',
        'quality',
        'age_restriction',
        'genre',
        'viewing_classification',
        'available_languages',
    ];
}
