<?php

namespace Database\Seeders;

use App\Http\Resources\LessonResource;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run() {
        $this->call([UserSeeder::class]);
        $this->call([FragmentSeeder::class]);
        $this->call([TagSeeder::class]);
        $this->call([LessonSeeder::class]);
    }
}
