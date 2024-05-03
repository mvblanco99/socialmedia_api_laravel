<?php

namespace Database\Seeders;

use App\Models\Image;
use App\Models\Post;
use App\Models\User;
use App\Services\ImageServices;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

    public function __construct(
    private ImageServices $imageServices
    ){}

    public function run(): void
    {

        $postMyUser = [];

        for ($i=0; $i < 250; $i++) { 
            $post = Post::create([
                'user_id' => 1,
                'is_edit' => Post::UNEDITED,
                'description' => fake()->sentence()
            ]);

            array_push($postMyUser,$post);

        }

        $postUserAleatorios = [];
        for ($i=2; $i < 250; $i++) { 
            $post = Post::create([
                'user_id' => $i,
                'is_edit' => Post::UNEDITED,
                'description' => fake()->sentence()
            ]);
            array_push($postUserAleatorios,$post);
        }

        $postUserAleatorios2 = [];
        for ($i=251; $i < 600; $i++) { 
            $post = Post::create([
                'user_id' => $i,
                'is_edit' => Post::UNEDITED,
                'description' => fake()->sentence()
            ]);
            array_push($postUserAleatorios2,$post);
        }

        for ($i=0; $i < count($postMyUser) ; $i++) { 
            Image::create([
                'url' => 'http://127.0.0.1:8000/profile_image_default.jpg',
                'post_id' => $postMyUser[$i]->id
            ]);
        }
        
    }
}
