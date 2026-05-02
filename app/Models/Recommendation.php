<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Recommendation extends Model
{
    use HasFactory;

    protected $fillable = ['child_id', 'title', 'description', 'category', 'is_completed'];

    protected $casts = ['is_completed' => 'boolean'];

    public function child() { return $this->belongsTo(Child::class); }
}
