<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FriendRequest extends Model
{
    use HasFactory;

    public const PENDING = '0';
    public const ACCEPTED = '1';

    protected $fillable = [
        'sender',
        'recipient',
        'status'
    ];
}
