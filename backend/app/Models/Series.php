<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Series extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'age_restriction',
        'release_date',
        'genre',
        'viewing_classification',
    ];

    /**
     * Define the relationship to the Season model.
     */
    public function seasons()
    {
        return $this->hasMany(Season::class, 'series_id', 'series_id');
    }
}
