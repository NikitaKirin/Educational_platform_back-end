<?php

declare(strict_types=1);

namespace App\Orchid\Screens;

use App\Models\AgeLimit;
use App\Models\Fragment;
use App\Models\Lesson;
use App\Models\User;
use App\Orchid\Layouts\AgeLimit\BarAgeLimits;
use App\Orchid\Layouts\Fragment\ChartPieFragments;
use App\Orchid\Layouts\Fragment\FragmentListLayout;
use App\Orchid\Layouts\Fragment\LineFragments;
use App\Orchid\Layouts\Lesson\LineLessons;
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
            'fragments'     => [
                Fragment::where('fragmentgable_type', 'LIKE', 'article')->countByDays(Carbon::now()->subDay(14),
                    Carbon::now())->toChart('Статьи'),
                Fragment::where('fragmentgable_type', 'LIKE', 'video')->countByDays(Carbon::now()->subDay(14),
                    Carbon::now())->toChart('Видео'),
                Fragment::where('fragmentgable_type', 'LIKE', 'image')->countByDays(Carbon::now()->subDay(14),
                    Carbon::now())->toChart('Изображения'),
                Fragment::where('fragmentgable_type', 'LIKE', 'game')->countByDays(Carbon::now()->subDay(14),
                    Carbon::now())->toChart('Игры'),
            ],
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
            'lessons'       => [
                Lesson::countByDays(Carbon::now()->subDay(14), Carbon::now())->toChart('Уроки'),
            ],
            'ageLimits'     => [
                [
                    'labels' => ['0+', '3+', '5+', '7+'],
                    'name'   => 'Фрагменты',
                    'values' => [
                        AgeLimit::where('text_context', 'LIKE', '0+')->first()->loadCount('fragments')
                            ->fragments_count,
                        AgeLimit::where('text_context', 'LIKE', '3+')->first()->loadCount('fragments')
                            ->fragments_count,
                        AgeLimit::where('text_context', 'LIKE', '5+')->first()->loadCount('fragments')
                            ->fragments_count,
                        AgeLimit::where('text_context', 'LIKE', '7+')->first()->loadCount('fragments')
                            ->fragments_count,
                    ],
                ],
                [
                    'labels' => ['0+', '3+', '5+', '7+'],
                    'name'   => 'Уроки',
                    'values' => [
                        AgeLimit::where('text_context', 'LIKE', '0+')->first()->loadCount('lessons')->lessons_count,
                        AgeLimit::where('text_context', 'LIKE', '3+')->first()->loadCount('lessons')->lessons_count,
                        AgeLimit::where('text_context', 'LIKE', '5+')->first()->loadCount('lessons')->lessons_count,
                        AgeLimit::where('text_context', 'LIKE', '7+')->first()->loadCount('lessons')->lessons_count,
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
            Layout::blank([
                Layout::columns([
                    BarUsersRoles::class,
                    LineUsers::class,
                ])
            ]),

            Layout::columns([
                BarAgeLimits::class,
                ChartPieFragments::class,
            ]),

            Layout::columns([
                    LineFragments::class,
                    LineLessons::class,
                ]
            ),
        ];
    }
}
