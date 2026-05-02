<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Routine extends Model
{
    use HasFactory;

    protected $table = 'routines';

    protected $fillable = [
        'child_id', 'user_id', 'day_of_week', 'time', 
        'activity', 'duration', 'completed', 'order_index'
    ];

    protected $casts = [
        'completed' => 'boolean',
        'duration' => 'integer'
    ];

    public function child()
    {
        return $this->belongsTo(Child::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}