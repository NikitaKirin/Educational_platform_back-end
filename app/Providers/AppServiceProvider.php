<?php

namespace App\Providers;

use App\Http\Resources\UserResource;
use App\Models\Article;
use App\Models\Image;
use App\Models\Test;
use App\Models\Video;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register() {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot() {
        Relation::morphMap([
            'article' => Article::class,
            'test'    => Test::class,
            'video'   => Video::class,
            'image'   => Image::class,
        ]);
    }
}
