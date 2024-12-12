<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Preference extends Model
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
    protected $primaryKey = 'preference_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'profile_id',
        'content_preference',
        'content_type',
        'genre',
        'minimum_age',
    ];

    /**
     * Define the relationship to the Profile model.
     */
    public function profile()
    {
        return $this->belongsTo(Profile::class, 'profile_id', 'profile_id');
    }
}
