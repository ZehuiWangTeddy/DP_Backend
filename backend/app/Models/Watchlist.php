<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Watchlist extends Model
{
    use HasFactory;

    public $timestamps = true; // Explicitly define the table name
protected $table = 'watchlists'; // Define custom primary key
protected $primaryKey = 'watchlist_id'; // Enable timestamps for created_at and updated_at
    protected $fillable = [
        'profile_id',
        'episode_id',
        'movie_id',
        'viewing_status',
    ];

    /**
     * Relationship to Profile.
     */
    public function profile()
    {
        return $this->belongsTo(Profile::class, 'profile_id', 'profile_id');
    }

    /**
     * Relationship to Episode.
     */
    public function episode()
    {
        return $this->belongsTo(Episode::class, 'episode_id', 'episode_id');
    }

    /**
     * Relationship to Movie.
     */
    public function movie()
    {
        return $this->belongsTo(Movie::class, 'movie_id', 'movie_id');
    }
}
