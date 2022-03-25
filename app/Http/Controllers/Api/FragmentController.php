<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Fragment\CreateFragmentRequest;
use App\Http\Requests\Api\Fragment\IndexFragmentRequest;
use App\Http\Requests\Api\Fragment\UpdateFragmentRequest;
use App\Http\Resources\FragmentResource;
use App\Http\Resources\FragmentResourceCollection;
use App\Models\Article;
use App\Models\Game;
use App\Models\GameType;
use App\Models\Image;
use App\Models\Tag;
use App\Models\Test;
use App\Models\Fragment;
use App\Models\User;
use App\Models\Video;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class FragmentController extends Controller
{
    // Вывести список фрагментов текущего пользователя.
    public function myIndex( IndexFragmentRequest $request ): FragmentResourceCollection {
        $title = $request->input('title');
        $type = $request->input('type');
        $tags = $request->input('tags');
        $fragments = Fragment::with('tags')->withCount('tags')->where('user_id', Auth::id())
                             ->when($title, function ( $query ) use ( $title ) {
                                 return $query->where('title', 'ILIKE', '%' . $title . '%');
                             })->when($type, function ( $query ) use ( $type ) {
                return $query->where('fragmentgable_type', 'ILIKE', '%' . $type . '%');
            })->when($tags, function ( $query ) use ( $tags ) {
                return $query->whereHas('tags', function ( $query ) use ( $tags ) {
                    $query->whereIntegerInRaw('tag_id', $tags);
                });
            });
        return new FragmentResourceCollection($fragments->orderBy('title')->paginate(6));
    }

    // Вывести список всех фрагментов. Функционал пользователя и администратора.
    public function index( IndexFragmentRequest $request ): FragmentResourceCollection {
        $title = $request->input('title');
        $type = $request->input('type');
        $tags = $request->input('tags');
        $fragments = Fragment::with('tags')->withCount('tags')->when($title, function ( $query ) use ( $title ) {
            return $query->where('title', 'ILIKE', '%' . $title . '%');
        })->when($type, function ( $query ) use ( $type ) {
            return $query->where('fragmentgable_type', 'ILIKE', '%' . $type . '%');
        })->when($tags, function ( $query ) use ( $tags ) {
            return $query->whereHas('tags', function ( $query ) use ( $tags ) {
                $query->whereIntegerInRaw('tag_id', $tags);
            });
        });
        return new FragmentResourceCollection($fragments->orderBy('title')->paginate(6));
    }

    /**
     * Create new fragment
     * Создать новый фрагмент
     * @param CreateFragmentRequest $request
     * @return JsonResponse
     */
    public function store( CreateFragmentRequest $request ): JsonResponse {
        $user = Auth::user();
        DB::transaction(function () use ( $request, $user ) {
            if ( $request->input('type') == 'article' ) {
                $fragmentData = $this->createFragmentArticle($request);
            }
            elseif ( $request->input('type') == 'video' ) {
                $fragmentData = $this->createFragmentVideo($request);
            }
            elseif ( $request->input('type') == 'image' ) {
                $fragmentData = $this->createFragmentImage($request);
            }
            elseif ( $request->input('type') == 'game' ) {
                $fragmentData = $this->createFragmentGame($request, $user);
            }
            $fragment = new Fragment(['title' => $request->input('title')]);
            $fragment->user()->associate(Auth::user());
            $fragment->fragmentgable()->associate($fragmentData);
            $fragment->save();
            $fragment->tags()->sync($request->input('tags'));
            if ( isset($request->fon) )
                $fragment->addMediaFromRequest('fon')->toMediaCollection('fragments_fons', 'fragments_fons');
        });
        return response()->json([
            'message' => 'Новый фрагмент успешно загружен!',
        ], 201);
    }

    // Получить определенный фрагмент. Функционал пользователя и администратора.
    public function show( Fragment $fragment ): FragmentResource {
        return new FragmentResource($fragment->load('tags')->loadCount('tags'));
    }

    /**
     * Update fragment
     * Обновить содержимое фрагмента. Функционал пользователя и администратора.
     * @param UpdateFragmentRequest $request
     * @param Fragment $fragment
     * @return JsonResponse
     */
    public function update( UpdateFragmentRequest $request, Fragment $fragment ): JsonResponse {
        DB::transaction(function () use ( $request, $fragment ) {
            if ( $tags = $request->input('tags') ) {
                $tags = array_unique($tags);
                $fragment->tags()->sync($tags);
            }
            else {
                DB::table('fragment_tag')->where('fragment_id', $fragment->id)->delete();
            }
            if ( $fragment->fragmentgable_type === 'video' || $fragment->fragmentgable_type === 'image' ) {
                $this->updateFragmentVideoOrImage($request, $fragment);
            }
            elseif ( $fragment->fragmentgable_type == 'article' ) {
                $this->updateFragmentArticle($request, $fragment);
            }
            elseif ( $fragment->fragmentgable_type == 'game' ) {
                $this->updateFragmentGame($request, $fragment);
            }
            if ( $request->hasFile('fon') ) {
                if ( empty($fragment->getFirstMediaUrl('fragments_fons')) )
                    $fragment->addMediaFromRequest('fon')->toMediaCollection('fragments_fons', 'fragments_fons');
                else {
                    $fragment->clearMediaCollection('fragments_fons');
                    $fragment->addMediaFromRequest('fon')->toMediaCollection('fragments_fons', 'fragments_fons');
                }
            }
        });
        return response()->json([
            'message' => 'Фрагмент успешно обновлен',
        ], 200);
    }


    // Удалить фрагмент. Функционал пользователя и администратора. Мягкое удаление.
    public function destroy( Fragment $fragment ): JsonResponse {

        if ( $fragment->loadCount('lessons')->lessons_count > 0 ) {
            $lessons = $fragment->lessons()->whereHas('user', function ( Builder $query ) {
                $query->where('role', '<>', 'student');
            })->orderBy('title')->get(['id', 'title']);

            /*if ( Auth::user()->role == 'admin' ) {
                return response()->json([
                    'message'               => "Невозможно удалить фрагмент",
                    'all_lessons_count'     => $fragment->lessons_count,
                    'teacher_lessons_count' => $lessons->count(),
                    'lessons'               => $lessons,
                ], 400);
            }*/
            return response()->json([
                'message'               => "Не удалось удалить фрагмент",
                "all_lessons_count"     => $fragment->lessons_count,
                "teacher_lessons_count" => $lessons->count(),
                'lessons'               => $lessons,
            ], 400);
        }
        if ( $fragment->delete() ) {
            return response()->json([
                'message' => 'Фрагмент успешно удалён!',
            ], 200);
        }

        return response()->json([
            'message' => 'Не удалось удалить фрагмент',
        ], 400);
    }

    // Добавить/удалить фрагмент из избранного. Функционал пользователя и администратора.
    public function like( Request $request, Fragment $fragment ) {
        $query = DB::table('fragment_user')->where('fragment_id', $fragment->id)->where('user_id', Auth::id());
        if ( $query->exists() )
            $query->delete();
        else
            Auth::user()->favouriteFragments()->attach($fragment->id);
        return response(['message' => 'Ok'], 200);
    }

    // Получить список избранных фрагментов. Функционал пользователя и администратора.
    public function likeIndex( Request $request ): FragmentResourceCollection {
        $title = $request->input('title');
        $type = $request->input('type');
        $tags = $request->input('tags');
        $fragments = Auth::user()->favouriteFragments()->withCount('tags')->with('tags')
                         ->when($title, function ( $query ) use ( $title ) {
                             return $query->where('title', 'ILIKE', '%' . $title . '%');
                         })->when($type, function ( $query ) use ( $type ) {
                return $query->where('fragmentgable_type', 'ILIKE', '%' . $type . '%');
            })->when($tags, function ( $query ) use ( $tags ) {
                return $query->whereHas('tags', function ( $query ) use ( $tags ) {
                    $query->whereIntegerInRaw('tag_id', $tags);
                });
            });

        return new FragmentResourceCollection($fragments->orderBy('title')->paginate(6));
    }

    // Получить список фрагментов текущего учителя.
    public function fragmentsTeacherIndex( Request $request, User $user ): FragmentResourceCollection {
        return new FragmentResourceCollection($user->fragments()->with('tags')->paginate(6));
    }

    /**
     * Create fragment of type "article"
     * Создать фрагмент типа "Статья"
     * @param CreateFragmentRequest $request Объект запроса
     * @return Article
     */
    private function createFragmentArticle( CreateFragmentRequest $request ): Article {
        $fragmentData = new Article(['content' => $request->input('content')]);
        $fragmentData->save();
        return $fragmentData;
    }

    /**
     * Create fragment of type "video"
     * Создать фрагмент типа "Видео"
     * @param CreateFragmentRequest $request Объект запроса
     * @return Video
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    private function createFragmentVideo( CreateFragmentRequest $request ): Video {
        $fragmentData = new Video();
        $fragmentData->content = '1';
        $fragmentData->save();
        $fragmentData->addMediaFromRequest('content')->toMediaCollection('fragments_videos', 'fragments');
        $fragmentData->content = $fragmentData->getFirstMediaUrl('fragments_videos');
        $fragmentData->save();
        return $fragmentData;
    }

    /**
     * Create fragment of type "image"
     * Создать фрагмент типа "изображение"
     * @param CreateFragmentRequest $request Объект запроса
     * @return Image
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    private function createFragmentImage( CreateFragmentRequest $request ): Image {
        $fragmentData = new Image();
        $fragmentData->annotation = $request->input('annotation');
        $fragmentData->content = '1';
        $fragmentData->save();
        $fragmentData->addMediaFromRequest('content')->toMediaCollection('fragments_images', 'fragments');
        $fragmentData->content = $fragmentData->getFirstMediaUrl('fragments_images');
        $fragmentData->save();
        return $fragmentData;
    }

    /**
     * Create fragment of type "Game"
     * Создать фрагмент типа "игра"
     * @param Request $request Объект запроса
     * @param User $user - Пользователь, создающий игру
     * @return Game
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    private function createFragmentGame( Request $request, User $user ): Game {
        $game = new Game();
        $gameType = GameType::where('title', $request->input('gameType'))->get()->first();
        $game->game_type_id = $gameType->id;
        $gameTask = $request->input('task');
        $content = [];
        if ( $gameTask !== null ) {
            $content['task']['text'] = $gameTask;
        }
        else {
            $content['task']['text'] = $gameType->description;
        }
        $content['task']['mediaUrl'] = "";
        $content['images'] = [];
        $game->content = json_encode($content);
        $game->save();
        $game->addMultipleMediaFromRequest(['content'])->each(function ( $file_adder ) use ( $user, $gameType ) {
            $fileName = str_slug("$user->name-" . "$gameType->title-" . Str::random(10)) . '.jpg';
            $file_adder->usingFileName($fileName)->toMediaCollection('fragments_games', 'fragments');
        });
        $dataImages = $game->getMedia('fragments_games');
        foreach ( $dataImages as $dataImage ) {
            $content['images'][] = $dataImage->getFullUrl();
        }
        $game->content = json_encode($content, JSON_UNESCAPED_SLASHES);
        $game->save();
        return $game;
    }

    /**
     * Update fragment of types: video or image
     * Обновить фрагмент типа видеоролик или изображение
     * @param Request $request
     * @param Fragment $fragment
     */
    private function updateFragmentVideoOrImage( UpdateFragmentRequest $request, Fragment $fragment ): void {
        if ( $request->input('title') )
            $fragment->update(['title' => $request->input('title')]);
        if ( $request->hasFile('content') ) {
            $fragment->fragmentgable->clearMediaCollection("fragments_{$fragment->fragmentgable_type}s");
            $fragment->fragmentgable->addMediaFromRequest('content')
                                    ->toMediaCollection("fragments_{$fragment->fragmentgable_type}s", 'fragments');
            $fragment->fragmentgable->update(['content' => $fragment->fragmentgable->getFirstMediaUrl("fragments_{$fragment->fragmentgable_type}s")]);
        }

        if ( $fragment->fragmentgable_type == 'image' ) {
            $fragment->fragmentgable->update(['annotation' => $request->input('annotation')]);
        }
    }

    /**
     * Update fragment of types article
     * Обновить фрагмент типа статья
     * @param UpdateFragmentRequest $request
     * @param Fragment $fragment
     */
    private function updateFragmentArticle( UpdateFragmentRequest $request, Fragment $fragment ): void {
        $request->validate(['content' => 'string'], ['string' => 'На вход ожидалась строка']);
        $fragment->update($request->only('title'));
        $fragment->fragmentgable->update($request->only('content'));
    }

    private function updateFragmentGame( UpdateFragmentRequest $request, Fragment $fragment ) {
        $user = Auth::user();
        $game = $fragment->fragmentgable;
        $requestFragmentDataLinks = collect($request->input('old_links')); // Обновленный контент: старые ссылки;
        $currentFragmentDataLinks = collect(json_decode($fragment->fragmentgable->content)); // Текущий контент - ссылки;
        $updatedFragmentDataLinks = $currentFragmentDataLinks->filter(function ( $link ) use ( $requestFragmentDataLinks ) {
            return $requestFragmentDataLinks->contains($link);
        });
        $game->getMedia('fragments_games')->each(function ( $image ) use ( $updatedFragmentDataLinks, $game ) {
            if ( !$updatedFragmentDataLinks->contains($image->getFullUrl()) ) {
                Media::whereId($image->id)->delete();
            }
        });
        $game->refresh();
        if ( $request->file('content') ) {
            $game->addMultipleMediaFromRequest(['content'])->each(function ( $image ) use ( $user, $game ) {
                $image->usingFileName("$user->name-" . "$game->type-" . Str::random('5') . '.jpg')
                      ->toMediaCollection('fragments_games', 'fragments');
            });
        }
        $game->refresh();
        $fragmentDataImages = $game->getMedia('fragments_games');
        foreach ( $fragmentDataImages as $gameImage ) {
            if ( !$updatedFragmentDataLinks->contains($gameImage->getFullUrl()) ) {
                $updatedFragmentDataLinks[] = $gameImage->getFullUrl();
            }
        }
        $game->update(['content' => json_encode($updatedFragmentDataLinks, JSON_UNESCAPED_SLASHES)]);
    }
}
