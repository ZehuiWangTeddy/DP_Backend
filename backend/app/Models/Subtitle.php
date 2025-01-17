<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subtitle extends Model
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
    protected $primaryKey = 'subtitle_id';

    protected $fillable = [
        'language',
        'movie_id',
        'episode_id',
        'subtitle_path',
    ];

    public function movie()
    {
        return $this->belongsTo(Movie::class);
    }

    public function episode()
    {
        return $this->belongsTo(Episode::class);
    }
}