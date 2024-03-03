<?php

namespace App\Observers;
use App\Models\Image;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ImageObserver
{
    public function create(Image $image)
    {
        $user = Auth::user()->id;
        $user->url_image_profile = $image->url;
        $user->save();
    }
}
