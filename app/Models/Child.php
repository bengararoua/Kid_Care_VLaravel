<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Child extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'age', 'parent_id', 'psychologist_id', 'teacher_id', 'notes'];

    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function psychologist()
    {
        return $this->belongsTo(User::class, 'psychologist_id');
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function behaviors()
    {
        return $this->hasMany(BehaviorLog::class);
    }

    public function recommendations()
    {
        return $this->hasMany(Recommendation::class);
    }

    public function psychologistNotes()
    {
        return $this->hasMany(PsychologistNote::class);
    }
}