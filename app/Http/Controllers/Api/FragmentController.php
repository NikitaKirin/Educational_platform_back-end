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
use JetBrains\PhpStorm\NoReturn;
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
            if ( $request->input('type') === 'article' ) {
                $fragmentData = $this->createFragmentArticle($request);
            }
            elseif ( $request->input('type') === 'video' ) {
                $fragmentData = $this->createFragmentVideo($request);
            }
            elseif ( $request->input('type') === 'image' ) {
                $fragmentData = $this->createFragmentImage($request);
            }
            elseif ( $request->input('type') === 'game' ) {
                if ( $request->input('gameType') === 'pairs' ) {
                    $fragmentData = $this->createFragmentGamePairs($request, $user);
                }
                elseif ( $request->input('gameType') === 'matchmaking' ) {
                    $fragmentData = $this->createFragmentGameMatchmaking($request, $user);
                }
            }
            $fragment = new Fragment(['title' => $request->input('title')]);
            $fragment->user()->associate(Auth::user());
            $fragment->fragmentgable()->associate($fragmentData);
            $fragment->save();
            $fragment->tags()->sync($request->input('tags'));
            if ( isset($request->fon) ) {
                $fragment->addMediaFromRequest('fon')->toMediaCollection('fragments_fons', 'fragments_fons');
            }
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
            if ( $title = $request->input('title') ) {
                $fragment->update(['title' => $title]);
            }
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
                $gameType = GameType::where('id', $fragment->fragmentgable->game_type_id)->get()->first();
                if ( $gameType->type === 'pairs' ) {
                    $this->updateFragmentGamePairs($request, $fragment);
                }
                elseif ( $gameType->type === 'matchmaking' ) {
                    $this->updateFragmentGameMatchmaking($request, $fragment);
                }
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
    private function createFragmentGamePairs( Request $request, User $user ): Game {
        $game = new Game();
        $gameType = GameType::where('type', $request->input('gameType'))->get()->first();
        $game->game_type_id = $gameType->id;
        $gameTask = $request->input('task');
        $content = ['gameType' => $gameType->type];
        if ( $gameTask !== null ) {
            $content['task']['text'] = $gameTask;
        }
        else {
            $content['task']['text'] = $gameType->description;
        }
        $content['task']['mediaUrl'] = "";
        $content['images'] = [];
        $game->content = json_encode($content, JSON_UNESCAPED_UNICODE);
        $game->save();
        $game->addMultipleMediaFromRequest(['content'])->each(function ( $file_adder ) use ( $user, $gameType ) {
            $fileName = str_slug("$user->name-" . "$gameType->type-" . Str::random(10)) . '.jpg';
            $file_adder->usingFileName($fileName)->toMediaCollection('fragments_games', 'fragments');
        });
        $dataImages = $game->getMedia('fragments_games');
        foreach ( $dataImages as $dataImage ) {
            $content['images'][] = $dataImage->getFullUrl();
        }
        $game->content = json_encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $game->save();
        return $game;
    }

    /**
     * Create fragment of type "Game" - "matchmaking"
     * Создать фрагмента типа "игра" - подтип - "ассоциация"
     * @param CreateFragmentRequest $request Объект запроса
     * @return Game Игра
     */
    private function createFragmentGameMatchmaking( CreateFragmentRequest $request, $user ): Game {
        $game = new Game();
        $gameType = GameType::where('type', $request->input('gameType'))->get()->first();
        $game->game_type_id = $gameType->id;
        $gameTask = $request->input('task') ?? $gameType->description;
        $content = ['gameType' => $gameType->type];
        $content['task']['text'] = $gameTask;
        $content['task']['url'] = "";
        $content['images'] = [];
        $game->content = json_encode($content, JSON_UNESCAPED_UNICODE);
        $game->save();
        $imagesCollection = collect(collect($request->allFiles())->only(['content'])->values()[0]);
        foreach ( $imagesCollection as $pair ) {
            $content['images'][] = collect($pair)->map(function ( $image ) use ( $user, $game, $gameType ) {
                $fileName = "matchmaking-" . $game->id . '-' . str_slug($user->name) . '-' . Str::random(10) . '.' . $image->extension();
                return $game->addMedia($image)->usingFileName($fileName)->preservingOriginal()
                            ->toMediaCollection('fragments_games', 'fragments')->getFullUrl();
            })->toArray();
        }
        $game->refresh();
        $game->content = json_encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
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
        $fragment->fragmentgable->update($request->only('content'));
    }

    /**
     * Update fragment game - pairs
     * Обновить фрагмент типа игра - парочки
     * @param UpdateFragmentRequest $request Объект запроса
     * @param Fragment $fragment Фрагмент
     */
    private function updateFragmentGamePairs( UpdateFragmentRequest $request, Fragment $fragment ): void {
        $user = Auth::user();
        $game = $fragment->fragmentgable;
        $gameType = GameType::whereId($fragment->fragmentgable->game_type_id)->get()->first();
        $gameContent = json_decode($game->content, true);
        $requestFragmentDataLinks = collect($request->input('oldLinks')); // Обновленный контент: старые ссылки;
        $currentFragmentDataLinks = collect(json_decode($fragment->fragmentgable->content, true)['images']); // Текущий
        // контент -
        // ссылки;
        $updatedFragmentDataLinks = $currentFragmentDataLinks->filter(function ( $link ) use ( $requestFragmentDataLinks ) {
            return $requestFragmentDataLinks->contains($link);
        })->values();
        $updatedFragmentDataImages = $game->getMedia('fragments_games')
                                          ->filter(function ( $image ) use ( $updatedFragmentDataLinks ) {
                                              return $updatedFragmentDataLinks->contains($image->getFullUrl());
                                          })->values();
        $game->clearMediaCollectionExcept('fragments_games', $updatedFragmentDataImages);
        $game->refresh();
        if ( $request->file('content') ) {
            $game->addMultipleMediaFromRequest(['content'])->each(function ( $file_adder ) use ( $user, $gameType ) {
                $fileName = str_slug("$user->name-" . "$gameType->type-" . Str::random(10)) . '.jpg';
                $file_adder->usingFileName($fileName)->toMediaCollection('fragments_games', 'fragments');
            });
        }
        $game->refresh();
        $fragmentDataImages = $game->getMedia('fragments_games');
        foreach ( $fragmentDataImages as $gameImage ) {
            if ( !$updatedFragmentDataLinks->contains($gameImage->getFullUrl()) ) {
                $updatedFragmentDataLinks[] = $gameImage->getFullUrl();
            }
        }
        $gameContent['images'] = $updatedFragmentDataLinks;
        if ( $task = $request->input('task') ) {
            $gameContent['task']['text'] = $task;
        }
        $game->update(['content' => json_encode($gameContent, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
    }

    private function updateFragmentGameMatchmaking( UpdateFragmentRequest $request, Fragment $fragment ) {
        $user = $fragment->user;
        $game = $fragment->fragmentgable;
        $gameType = GameType::whereId($fragment->fragmentgable->game_type_id)->get()->first();
        $imagesCollection = collect(collect($request->only(['content']))->values()[0]);
        $content = ['gameType' => $gameType->type];
        $content['task']['text'] = $request->input('task') ?? $gameType->description;
        $content['task']['url'] = "";
        foreach ( $imagesCollection as $pair ) {
            $content['images'][] = collect($pair)->map(function ( $image ) use ( $user, $game, $gameType ) {
                if ( gettype($image) === 'string' ) {
                    return $image;
                }
                $fileName = "matchmaking-" . $game->id . '-' . str_slug($user->name) . '-' . Str::random(10) . '.' . $image->extension();
                return $game->addMedia($image)->usingFileName($fileName)->preservingOriginal()
                            ->toMediaCollection('fragments_games', 'fragments')->getFullUrl();
            })->toArray();
        }
        $updatedLinks = collect($content['images'])->collapse();
        $updatedGameImages = $game->getMedia('fragments_games')->filter(function ( $image ) use ( $updatedLinks ) {
            return $updatedLinks->contains($image->getFullUrl());
        })->values();
        $game->clearMediaCollectionExcept('fragments_games', $updatedGameImages);
        $game->refresh();
        $game->update(['content' => json_encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
    }
}
