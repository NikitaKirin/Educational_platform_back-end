<?php

namespace App\Providers;

use App\Models\Fragment;
use App\Models\Lesson;
use App\Models\User;
use App\Policies\FragmentPolicy;
use App\Policies\LessonPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [// 'App\Models\Model' => 'App\Policies\ModelPolicy',
                           User::class     => UserPolicy::class,
                           Fragment::class => FragmentPolicy::class,
                           Lesson::class   => LessonPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot() {
        $this->registerPolicies();

        if ( !$this->app->routesAreCached() ) {
            Passport::routes();
        }
    }
}
