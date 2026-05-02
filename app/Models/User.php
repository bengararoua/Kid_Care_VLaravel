<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'points',
        'last_log_date'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isParent() {
        return $this->role === 'parent';
    }

    public function isTeacher() {
        return $this->role === 'teacher';
    }

    public function isPsychologist() {
        return $this->role === 'psychologist';
    }
    
    public function children()
    {
        return $this->hasMany(Child::class, 'parent_id');
    }
    
    public function assignedChildren()
    {
        return $this->hasMany(Child::class, 'psychologist_id');
    }
}