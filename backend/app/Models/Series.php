<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Series extends Model
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
    protected $primaryKey = 'series_id';

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
