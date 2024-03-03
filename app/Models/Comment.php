<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Comment extends Model
{
    use HasFactory;

    const EDITED = '1';
    const UNEDITED = '0';

    protected $fillable = [
        'paragraph',
        'commentable_id',
        'commentable_type',
        'user_id',
        'is_edit'
    ];

    //Relacion de uno a muchos inversa
    public function user():BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comment():MorphMany
    {
        return $this->morphMany(Comment::class,'commentable');
    }

    public function reaction():MorphMany
    {
        return $this->morphMany(Reaction::class,'reactionable');
    }

    public function commentable():MorphTo
    {
        return $this->morphTo();
    }
}
