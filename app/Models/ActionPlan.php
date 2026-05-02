<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ActionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'child_id',
        'risk_level',
        'generated_date',
        'morning_activities',
        'afternoon_activities',
        'evening_activities',
        'communication_tips',
        'games_activities'
    ];

    protected $casts = [
        'morning_activities' => 'array',
        'afternoon_activities' => 'array',
        'evening_activities' => 'array',
        'communication_tips' => 'array',
        'games_activities' => 'array',
        'generated_date' => 'date'
    ];

    public function child()
    {
        return $this->belongsTo(Child::class);
    }
}