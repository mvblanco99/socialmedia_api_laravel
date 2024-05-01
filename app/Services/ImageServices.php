<?php

namespace App\Services;

use App\Http\Requests\ImageRequest;
use App\Models\Image;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ImageServices
{

  public function move(UploadedFile $image, string $user_id)
  {
    $user = User::find($user_id);
    $filename = time() . '.' . $image->extension();
    $image->storeAs('public/Images/'. $user->name . "" . $user->id .   '/',$filename);
    return $filename;
  }

  public function createImage(string $filename, Post $post)
  {
    $imageRequest = new ImageRequest();
    $authorize = $imageRequest->merge([$filename,$post->id]);
    $newImage = null;

    if($authorize){
      $newImage = Image::create([
        'post_id' => $post->id,
        'url' => 
          url(Storage::url('public/Images/' . "" .$post->user->name . "" . $post->user_id . "/". $filename ))
      ]);
    }
    return $newImage;
  }

}
