<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
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
    protected $primaryKey = 'subscription_id';

    protected $fillable = [
        'user_id',
        'price',
        'name',
        'status',
        'start_date',
        'end_date',
        'payment_method',
    ];

    /**
     * Define the relationship to the User model.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
