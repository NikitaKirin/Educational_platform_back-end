<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    public function run() {
        DB::table('users')->insert([
            'name'     => 'Иванов Иван Иванович',
            'birthday' => '01.01.2001',
            'role'     => 'admin',
            'email'    => 'ivanov@mail.ru',
            'password' => 'admin',
        ]);
    }
}
