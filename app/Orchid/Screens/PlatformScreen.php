<?php

declare(strict_types=1);

namespace App\Orchid\Screens;

use App\Models\Fragment;
use App\Models\User;
use App\Orchid\Layouts\Fragment\ChartPieFragments;
use App\Orchid\Layouts\Fragment\FragmentListLayout;
use App\Orchid\Layouts\User\BarUsersRoles;
use App\Orchid\Layouts\User\LineUsers;
use Carbon\Carbon;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Orchid\Platform\Models\Role;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class PlatformScreen extends Screen
{
    /**
     * Query data.
     *
     * @return array
     */
    public function query(): iterable {
        return [
            'fragments'     => Fragment::latest('created_at')->limit(5)->get(),
            'fragmentsType' => [
                [
                    'labels' => ['Статья', 'Видео', 'Изображение', 'Игра'],
                    'name'   => ['Типы фрагментов'],
                    'values' => [
                        Fragment::where('fragmentgable_type', 'LIKE', 'article')->count(),
                        Fragment::where('fragmentgable_type', 'LIKE', 'video')->count(),
                        Fragment::where('fragmentgable_type', 'LIKE', 'image')->count(),
                        Fragment::where('fragmentgable_type', 'LIKE', 'game')->count(),
                    ],
                ],
            ],
            'users'         => [
                User::countByDays(Carbon::now()->subDay(7), Carbon::now())
                    ->toChart('Users'),
                //Role::countByDays()->toChart('Roles'),
            ],
            'userRoles'     => [
                [
                    'labels' => ['Ученики', 'Учителя', 'Администраторы'],
                    'name'   => 'Количество',
                    'values' => [
                        User::whereHas('roles', function ( Builder $query ) {
                            return $query->where('slug', 'LIKE', 'student');
                        })->count(),
                        User::whereHas('roles', function ( Builder $query ) {
                            return $query->where('slug', 'LIKE', 'creator');
                        })->count(),
                        User::whereHas('roles', function ( Builder $query ) {
                            return $query->where('slug', 'LIKE', 'admin');
                        })->count(),
                    ],
                ],
            ],
        ];
    }

    /**
     * Display header name.
     *
     * @return string|null
     */
    public function name(): ?string {
        return 'Добро пожаловать, ' . Auth::user()->name . '!';
    }

    /**
     * Display header description.
     *
     * @return string|null
     */
    public function description(): ?string {
        return 'Youngeek platform';
    }

    /**
     * Button commands.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable {
        return [
            Link::make('Основной сайт')
                ->href('https://youngeek.na4u.ru/')
                ->icon('globe-alt'),
            /*
                        Link::make('Documentation')
                            ->href('https://orchid.software/en/docs')
                            ->icon('docs'),

                        Link::make('GitHub')
                            ->href('https://github.com/orchidsoftware/platform')
                            ->icon('social-github'),*/
        ];
    }

    /**
     * Views.
     *
     * @return \Orchid\Screen\Layout[]
     */
    public function layout(): iterable {
        return [
            //Layout::view('platform::partials.welcome'),
            Layout::columns([
                ChartPieFragments::class,
                LineUsers::class,
            ]),

            Layout::columns([
                BarUsersRoles::class,
            ]),

            FragmentListLayout::class,
        ];
    }
}
