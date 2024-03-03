<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Reaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'reactionable_id',
        'reactionable_type'
    ];

    //Relacion de uno a muchos inversa
    public function user():BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reactionable():MorphTo
    {
        return $this->morphTo();
    }
}
