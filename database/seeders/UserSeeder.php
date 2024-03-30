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
        $myUser = User::create([
            'name' => 'Manuel',
            "lastname" => 'Blanco',
            'email' => 'manuelvalentin1999@gmail.com',
            'password' => Hash::make('123456')
        ]);
        
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
