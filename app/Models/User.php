<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'lastname',
        'url_image_profile',
        'url_image_cover',
        'biography',
        'age',
        'address',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    //Relaciones de uno a muchos
    public function posts():HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function comments():HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function reactions():HasMany
    {
        return $this->hasMany(Reaction::class);
    }

    public function images():HasManyThrough
    {
        return $this->hasManyThrough(Image::class, Post::class);
    }

    public function friendsOfUser1()
    {
        return $this->hasMany(Friend::class, 'id_user', 'id');
    }

    public function friendsOfUser2()
    {
        return $this->hasMany(Friend::class, 'id_user2', 'id');
    }
}
