<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Friend extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender',
        'recipient',
    ];

    public function user1()
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }

    public function user2()
    {
        return $this->belongsTo(User::class, 'id_user2', 'id');
    }
    
}
