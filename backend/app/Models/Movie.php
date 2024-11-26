<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Movie extends Model
{
    use HasFactory;

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
