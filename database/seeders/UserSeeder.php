<?php

namespace Database\Seeders;

use Carbon\Carbon;
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
        for ( $i = 0; $i < 50; $i++ ) {
            DB::table('users')->insert([
                'name'       => Str::random(10),
                'role'       => 'creator',
                'email'      => Str::random('10') . '@gmail.ru',
                'password'   => Hash::make('password'),
                'blocked_at' => Carbon::now()->toDateTimeString(),
            ]);
        }

    }
}
