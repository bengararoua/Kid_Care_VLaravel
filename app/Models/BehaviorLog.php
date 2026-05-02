<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BehaviorLog extends Model
{
    use HasFactory;
    protected $fillable = [
        'child_id',
        'user_id',
        'focus_level',
        'mood',
        'sleep_hours',
        'social_interaction',
        'note',
        'log_date'
    ];

    protected $casts = [
        'focus_level' => 'integer',
        'sleep_hours' => 'decimal:2',
        'social_interaction' => 'integer',
        'log_date' => 'date'
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