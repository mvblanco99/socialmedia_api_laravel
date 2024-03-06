<?php

namespace App\Observers;

use App\Models\Friend;
use App\Services\UserServices;
use App\Traits\CacheTraits;
use Illuminate\Support\Facades\Cache;

class FriendObserver
{
    use CacheTraits;

    private const PREFIXKEYFRIEDSCACHE = "friendsofID=";

    public function created(Friend $friend)
    {
        Cache::flush();
    }
}
