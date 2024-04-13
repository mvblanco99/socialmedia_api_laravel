<?php

namespace Database\Seeders;

use App\Models\Post;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        for ($i=0; $i < 250; $i++) { 
            Post::create([
                'user_id' => 1,
                'is_edit' => Post::UNEDITED,
                'description' => 'my post'
            ]);
        }
    }
}
