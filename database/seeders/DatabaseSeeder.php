<?php

namespace Database\Seeders;

use App\Http\Resources\LessonResource;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run() {
        /*User::factory(10)->state(new Sequence(
            ['role' => 'student'],
            ['role' => 'creator']
        ))->create();*/
        $this->call([UserSeeder::class]);
        $this->call([AgeLimitSeeder::class]);
        $this->call([TagSeeder::class]);
        $this->call([GameTypeSeeder::class]);
        $this->call([FragmentSeeder::class]);
        $this->call([LessonSeeder::class]);
    }
}
