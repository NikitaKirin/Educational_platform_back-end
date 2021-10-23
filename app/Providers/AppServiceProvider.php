<?php

namespace App\Providers;

use App\Http\Resources\UserResource;
use App\Models\Article;
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
        UserResource::wrap('users');
        Relation::morphMap([
            'Article' => Article::class,
        ]);
    }
}
