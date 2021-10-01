<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run() {
        for ( $i = 0; $i < 100; $i++ ) {
            DB::table('users')->insert([
                'name'     => Str::random(10),
                'role'     => 'student',
                'email'    => Str::random('10') . '@gmail.ru',
                'password' => Hash::make('password'),
            ]);
        }

    }
}
