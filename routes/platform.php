<?php

declare(strict_types=1);

use App\Models\AgeLimit;
use App\Models\Fragment;
use App\Models\GameType;
use App\Models\Lesson;
use App\Models\Tag;
use App\Orchid\Screens\AgeLimit\AgeLimitEditScreen;
use App\Orchid\Screens\AgeLimit\AgeLimitListScreen;
use App\Orchid\Screens\Examples\ExampleCardsScreen;
use App\Orchid\Screens\Examples\ExampleChartsScreen;
use App\Orchid\Screens\Examples\ExampleFieldsAdvancedScreen;
use App\Orchid\Screens\Examples\ExampleFieldsScreen;
use App\Orchid\Screens\Examples\ExampleLayoutsScreen;
use App\Orchid\Screens\Examples\ExampleScreen;
use App\Orchid\Screens\Examples\ExampleTextEditorsScreen;
use App\Orchid\Screens\Fragment\FragmentEditScreen;
use App\Orchid\Screens\Fragment\FragmentListScreen;
use App\Orchid\Screens\Fragment\FragmentProfileScreen;
use App\Orchid\Screens\GameType\GameTypeEditScreen;
use App\Orchid\Screens\GameType\GameTypeListScreen;
use App\Orchid\Screens\Lesson\LessonListScreen;
use App\Orchid\Screens\Lesson\LessonProfileScreen;
use App\Orchid\Screens\PlatformScreen;
use App\Orchid\Screens\Role\RoleEditScreen;
use App\Orchid\Screens\Role\RoleListScreen;
use App\Orchid\Screens\Tag\TagEditScreen;
use App\Orchid\Screens\Tag\TagListScreen;
use App\Orchid\Screens\User\UserEditScreen;
use App\Orchid\Screens\User\UserListScreen;
use App\Orchid\Screens\User\UserProfileScreen;
use Illuminate\Support\Facades\Route;
use Tabuna\Breadcrumbs\Trail;

/*
|--------------------------------------------------------------------------
| Dashboard Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the need "dashboard" middleware group. Now create something great!
|
*/

// Main
Route::screen('/main', PlatformScreen::class)
     ->name('platform.main');

// Platform > Profile
Route::screen('profile', UserProfileScreen::class)
     ->name('platform.profile')
     ->breadcrumbs(function ( Trail $trail ) {
         return $trail
             ->parent('platform.index')
             ->push(__('Profile'), route('platform.profile'));
     });

// Platform > System > Users
Route::screen('users/{user}/edit', UserEditScreen::class)
     ->name('platform.systems.users.edit')
     ->breadcrumbs(function ( Trail $trail, $user ) {
         return $trail
             ->parent('platform.systems.users')
             ->push(__('User'), route('platform.systems.users.edit', $user));
     });

// Platform > System > Users > Create
Route::screen('users/create', UserEditScreen::class)
     ->name('platform.systems.users.create')
     ->breadcrumbs(function ( Trail $trail ) {
         return $trail
             ->parent('platform.systems.users')
             ->push(__('Create'), route('platform.systems.users.create'));
     });

// Platform > System > Users > User
Route::screen('users', UserListScreen::class)
     ->name('platform.systems.users')
     ->breadcrumbs(function ( Trail $trail ) {
         return $trail
             ->parent('platform.index')
             ->push(__('Users'), route('platform.systems.users'));
     });

// Platform > System > Roles > Role
Route::screen('roles/{role}/edit', RoleEditScreen::class)
     ->name('platform.systems.roles.edit')
     ->breadcrumbs(function ( Trail $trail, $role ) {
         return $trail
             ->parent('platform.systems.roles')
             ->push(__('Role'), route('platform.systems.roles.edit', $role));
     });

// Platform > System > Roles > Create
Route::screen('roles/create', RoleEditScreen::class)
     ->name('platform.systems.roles.create')
     ->breadcrumbs(function ( Trail $trail ) {
         return $trail
             ->parent('platform.systems.roles')
             ->push(__('Create'), route('platform.systems.roles.create'));
     });

// Platform > System > Roles
Route::screen('roles', RoleListScreen::class)
     ->name('platform.systems.roles')
     ->breadcrumbs(function ( Trail $trail ) {
         return $trail
             ->parent('platform.index')
             ->push(__('Roles'), route('platform.systems.roles'));
     });

// Platform > System > Tags
Route::screen('tags', TagListScreen::class)
     ->name('platform.systems.tags')
     ->breadcrumbs(function ( trail $trail ) {
         return $trail
             ->parent('platform.index')
             ->push(__('Теги'), route('platform.systems.tags'));
     });

// Platform > System > Tags > Create
Route::screen('tags/create', TagEditScreen::class)
     ->name('platform.systems.tags.create')
     ->breadcrumbs(function ( trail $trail ) {
         return $trail
             ->parent('platform.systems.tags')
             ->push(__('Создать'), route('platform.systems.tags.create'));
     });

// Platform > System > Tags > Edit
Route::screen('tags/{tag}/edit', TagEditScreen::class)
     ->name('platform.systems.tags.edit')
     ->breadcrumbs(function ( trail $trail, Tag $tag ) {
         return $trail
             ->parent('platform.systems.tags')
             ->push(__('Edit'), route('platform.systems.tags.edit', $tag));
     });

// Platform > System > GameTypes
Route::screen('gameTypes', GameTypeListScreen::class)
     ->name('platform.systems.gameTypes')
     ->breadcrumbs(function ( trail $trail ) {
         return $trail
             ->parent('platform.index')
             ->push(__('Типы игр'), route('platform.systems.gameTypes'));
     });

// Platform > System > GameTypes > Create
Route::screen('gameTypes/create', GameTypeEditScreen::class)
     ->name('platform.systems.gameTypes.create')
     ->breadcrumbs(function ( trail $trail ) {
         return $trail
             ->parent('platform.systems.gameTypes')
             ->push(__('Тип игры'), route('platform.systems.gameTypes.create'));
     });

// Platform > Systems > GameTypes > Edit
Route::screen('gameTypes/{gameType}/edit', GameTypeEditScreen::class)
     ->name('platform.systems.gameTypes.edit')
     ->breadcrumbs(function ( trail $trail, GameType $gameType ) {
         return $trail
             ->parent('platform.systems.gameTypes')
             ->push(__('Тип игры'), route('platform.systems.gameTypes.edit', $gameType));
     });

// Platform > Systems > AgeLimits
Route::screen('ageLimits', AgeLimitListScreen::class)
     ->name('platform.systems.ageLimits')
     ->breadcrumbs(function ( trail $trail ) {
         return $trail
             ->parent('platform.index')
             ->push(__('Возрастные цензы'), route('platform.systems.ageLimits'));
     });

// Platform > Systems > AgeLimits > Edit
Route::screen('ageLimits/{ageLimit}/edit', AgeLimitEditScreen::class)
     ->name('platform.systems.ageLimits.edit')
     ->breadcrumbs(function ( trail $trail, AgeLimit $ageLimit ) {
         return $trail
             ->parent('platform.systems.ageLimits')
             ->push(__('Возрастной ценз'), route('platform.systems.ageLimits.edit', $ageLimit));
     });

// Platform > Systems > AgeLimits > Create
Route::screen('ageLimits/create', AgeLimitEditScreen::class)
     ->name('platform.systems.ageLimits.create')
     ->breadcrumbs(function ( trail $trail ) {
         return $trail
             ->parent('platform.systems.ageLimits')
             ->push(__('Возрастной ценз'), route('platform.systems.ageLimits.create'));
     });

// Platform > Systems > Fragments
Route::screen('fragments', FragmentListScreen::class)
     ->name('platform.systems.fragments')
     ->breadcrumbs(function ( Trail $trail ) {
         return $trail
             ->parent('platform.index')
             ->push(__('Все фрагменты'), route('platform.systems.fragments'));
     });

// Platform > Systems > Fragments > Edit
Route::screen('fragments/{fragment}/edit', FragmentEditScreen::class)
     ->name('platform.systems.fragments.edit')
     ->breadcrumbs(function ( Trail $trail, Fragment $fragment ) {
         return $trail
             ->parent('platform.systems.fragments')
             ->push(__('Изменить фрагмент'), route('platform.systems.fragments.edit', ["fragment" => $fragment->id]));
     });

// Platform > Systems > Fragments > Profile
Route::screen('fragments/{fragment}/profile', FragmentProfileScreen::class)
     ->name('platform.systems.fragments.profile')
     ->breadcrumbs(function ( Trail $trail, Fragment $fragment ) {
         return $trail
             ->parent('platform.systems.fragments')
             ->push('Фрагмент', route('platform.systems.fragments.profile', $fragment));
     });

// Platform > Systems > Lessons
Route::screen('lessons', LessonListScreen::class)
     ->name('platform.systems.lessons')
     ->breadcrumbs(function ( Trail $trail ) {
         return $trail
             ->parent('platform.index')
             ->push(__('Уроки'), route('platform.systems.lessons'));
     });

// Platform > Systems > Fragments > Profile
Route::screen('lessons/{lesson}/profile', LessonProfileScreen::class)
     ->name('platform.systems.lessons.profile')
     ->breadcrumbs(function ( Trail $trail, Lesson $lesson ) {
         return $trail
             ->parent('platform.systems.lessons')
             ->push('Урок', route('platform.systems.lessons.profile', $lesson));
     });

// Example...
Route::screen('example', ExampleScreen::class)
     ->name('platform.example')
     ->breadcrumbs(function ( Trail $trail ) {
         return $trail
             ->parent('platform.index')
             ->push('Example screen');
     });

Route::screen('example-fields', ExampleFieldsScreen::class)->name('platform.example.fields');
Route::screen('example-layouts', ExampleLayoutsScreen::class)->name('platform.example.layouts');
Route::screen('example-charts', ExampleChartsScreen::class)->name('platform.example.charts');
Route::screen('example-editors', ExampleTextEditorsScreen::class)->name('platform.example.editors');
Route::screen('example-cards', ExampleCardsScreen::class)->name('platform.example.cards');
Route::screen('example-advanced', ExampleFieldsAdvancedScreen::class)->name('platform.example.advanced');

//Route::screen('idea', Idea::class, 'platform.screens.idea');
