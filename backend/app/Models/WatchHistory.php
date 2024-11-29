<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WatchHistory extends Model
{
    use HasFactory;

    protected $table = 'watchhistories';

    protected $fillable = [
        'profile_id',
        'episode_id',
        'movie_id',
        'resume_to',
        'times_watched',
        'watched_time_stamp',
        'viewing_status',
    ];

    public $timestamps = false; // Disable timestamps if not needed

    public function profile()
    {
        return $this->belongsTo(Profile::class, 'profile_id', 'profile_id');
    }

    public function episode()
    {
        return $this->belongsTo(Episode::class, 'episode_id', 'episode_id');
    }

    public function movie()
    {
        return $this->belongsTo(Movie::class, 'movie_id', 'movie_id');
    }
}
