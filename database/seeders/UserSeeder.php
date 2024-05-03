<?php

namespace Database\Seeders;

use App\Models\Friend;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // $myUser = User::create([
        //     'name' => 'Manuel',
        //     "lastname" => 'Blanco',
        //     'email' => 'manuelvalentin1999@gmail.com',
        //     'password' => Hash::make('123456'),
        //     'url_image_profile' => 'http://127.0.0.1:8000/profile_image_default.jpg',
        //     'url_image_cover' => 'http://127.0.0.1:8000/image_cover_default.jpg'
            
        // ]);
        
        $myUser = User::find(1);
        User::factory()->count(3000)->create();
        $users = User::take(1200)->where('id', '!=', $myUser->id)->get();

        foreach($users as $user){
            Friend::create([
                'sender' => $myUser->id,
                'recipient' => $user->id
            ]);
        }
    }
}
