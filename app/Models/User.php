<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Like;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    // ---------------- FILLABLE -----------------
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'gender',
        'description',
        'profile_image',
        'age',
    ];

    // ---------------- HIDDEN -----------------
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // ---------------- CASTS -----------------
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // ---------------- MATCHES (optional pivot table) -----------------
    public function matches()
    {
        return $this->belongsToMany(
            User::class,
            'matches',        // pivot table name
            'user_id',        // this user's id
            'matched_user_id' // other user's id
        );
    }

    public function matchedBy()
    {
        return $this->belongsToMany(
            User::class,
            'matches',
            'matched_user_id',
            'user_id'
        );
    }

    // ---------------- LIKES -----------------
    // Likes this user has given
    public function likesGiven()
    {
        return $this->hasMany(Like::class, 'user_id');
    }

    // Likes this user has received
    public function likesReceived()
    {
        return $this->hasMany(Like::class, 'liked_user_id');
    }
}
