<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'description',
        'is_edit',
        'user_id'
    ];

    const EDITED = '1';
    const UNEDITED = '0';

    //Relacion de uno a muchos inversa
    public function user():BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function images():HasMany
    {
        return $this->hasMany(Image::class);
    }

    public function comment():MorphMany
    {
        return $this->morphMany(Comment::class,'commentable');
    }

    public function reaction():MorphMany
    {
        return $this->morphMany(Reaction::class,'reactionable');
    }
}
