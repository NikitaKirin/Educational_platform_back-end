<?php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Orchid\Platform\Models\Role;

class UserSeeder extends Seeder
{
    public function run() {
        $studentRole = Role::firstWhere('slug', '=', 'student');
        $creatorRole = Role::firstWhere('slug', '=', 'creator');
        for ( $i = 0; $i < 30; $i++ ) {
            $user = User::create([
                'name'     => Str::random(10),
                'email'    => Str::random('10') . '@gmail.ru',
                'password' => Hash::make('password'),
            ]);
            $user->addRole($studentRole)->save();
        }
        for ( $i = 0; $i < 30; $i++ ) {
            $user = User::create([
                'name'     => Str::random(10),
                'email'    => Str::random('10') . '@gmail.ru',
                'password' => Hash::make('password'),
            ]);
            $user->addRole($creatorRole)->save();
        }
    }
}
