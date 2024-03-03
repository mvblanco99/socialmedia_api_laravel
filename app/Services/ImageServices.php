<?php

namespace App\Services;

use App\Http\Requests\ImageRequest;
use App\Models\Image;
use App\Models\Post;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ImageServices
{

  public function move(UploadedFile $image, Post $post)
  {
    $filename = time() . '.' . $image->extension();
    $image->storeAs('public/images/'. $post->user->name . "" . $post->user_id .   '/',$filename);
    return $filename;
  }

  public function createImage(string $filename, Post $post)
  {
    $arrayData = [$filename, $post->id];
    $imageRequest = new ImageRequest();
    $authorize = $imageRequest->merge($arrayData);

    $image = null;

    if($authorize){
      $image = Image::create([
        'post_id' => $post->id,
        'url' => url(Storage::url('public/images/' . "" .$post->user->name . "" . $post->user_id . "/". $filename ))
      ]);
    }

    return $image;
  }

}