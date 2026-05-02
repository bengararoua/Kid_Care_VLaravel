<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PsychologistNote extends Model
{
    use HasFactory;

    protected $fillable = ['child_id', 'psychologist_id', 'note', 'session_date'];

    protected $casts = ['session_date' => 'date'];

    public function child()        { return $this->belongsTo(Child::class); }
    public function psychologist() { return $this->belongsTo(User::class, 'psychologist_id'); }
}
