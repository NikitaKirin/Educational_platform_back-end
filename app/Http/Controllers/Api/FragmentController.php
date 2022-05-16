<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Fragment\CreateFragmentRequest;
use App\Http\Requests\Api\Fragment\IndexFragmentRequest;
use App\Http\Requests\Api\Fragment\UpdateFragmentRequest;
use App\Http\Resources\FragmentResource;
use App\Http\Resources\FragmentResourceCollection;
use App\Models\AgeLimit;
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
use Illuminate\Http\File;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\NoReturn;
use phpDocumentor\Reflection\Types\Object_;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use function GuzzleHttp\Promise\all;

class FragmentController extends Controller
{
    /**
     * Get auth user`s fragments
     * Получить фрагменты текущего авторизованного пользователя
     * @param IndexFragmentRequest $request
     * @return FragmentResourceCollection
     */
    public function myIndex( IndexFragmentRequest $request ): FragmentResourceCollection {
        $title = $request->input('title');
        $type = $request->input('type');
        $tags = $request->input('tags');
        $ageLimit = $request->input('ageLimit');
        $fragments = Fragment::with('tags')->withCount('tags')->where('user_id', Auth::id())
                             ->when($title, function ( $query ) use ( $title ) {
                                 return $query->where('title', 'ILIKE', '%' . $title . '%');
                             })->when($type, function ( $query ) use ( $type ) {
                return $query->where('fragmentgable_type', 'ILIKE', '%' . $type . '%');
            })->when($ageLimit, function ( $query ) use ( $ageLimit ) {
                return $query->whereHas('ageLimit', function ( $query ) use ( $ageLimit ) {
                    return $query->where('id', 'LIKE', $ageLimit);
                });
            })->when($tags, function ( $query ) use ( $tags ) {
                return $query->whereHas('tags', function ( $query ) use ( $tags ) {
                    $query->whereIntegerInRaw('tag_id', $tags);
                });
            });
        return new FragmentResourceCollection($fragments->orderBy('title')->paginate(6));
    }

    // Вывести список всех фрагментов. Функционал пользователя и администратора.

    /**
     * Get all fragments
     * Получить список всех фрагментов на платформе
     * @param IndexFragmentRequest $request
     * @return FragmentResourceCollection
     */
    public function index( IndexFragmentRequest $request ): FragmentResourceCollection {
        $title = $request->input('title');
        $type = $request->input('type');
        $tags = $request->input('tags');
        $ageLimit = $request->input('ageLimit');
        $fragments = Fragment::with('tags')->withCount('tags')->when($title, function ( $query ) use ( $title ) {
            return $query->where('title', 'ILIKE', '%' . $title . '%');
        })->when($type, function ( $query ) use ( $type ) {
            return $query->where('fragmentgable_type', 'ILIKE', '%' . $type . '%');
        })->when($ageLimit, function ( $query ) use ( $ageLimit ) {
            return $query->whereHas('ageLimit', function ( $query ) use ( $ageLimit ) {
                return $query->where('id', 'LIKE', $ageLimit);
            });
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
                if ( $request->input('gameType') === 'pairs' || $request->input('gameType') === 'sequences' ) {
                    $fragmentData = $this->createFragmentGamePairsOrSequences($request, $user);
                }
                elseif ( $request->input('gameType') === 'matchmaking' ) {
                    $fragmentData = $this->createFragmentGameMatchmaking($request, $user);
                }
                elseif ( $request->input('gameType') === 'puzzles' ) {
                    $fragmentData = $this->createFragmentGamePuzzles($request, $user);
                }
            }
            $fragment = new Fragment(['title' => $request->input('title')]);
            $fragment->user()->associate(Auth::user());
            $fragment->fragmentgable()->associate($fragmentData);
            $ageLimitId = $request->input('ageLimit');
            $fragment->ageLimit()->associate(AgeLimit::find($ageLimitId));
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
            $user = Auth::user();
            if ( $title = $request->input('title') ) {
                $fragment->update(['title' => $title]);
            }
            if ( $ageLimitId = $request->input('ageLimit') ) {
                $fragment->ageLimit()->associate($ageLimitId);
            }
            $fragment->save();
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
                    $this->updateFragmentGamePairs($request, $fragment, $user);
                }
                elseif ( $gameType->type === 'matchmaking' ) {
                    $this->updateFragmentGameMatchmaking($request, $fragment);
                }
                elseif ( $gameType->type === 'sequences' ) {
                    $this->updateFragmentGameSequences($request, $fragment);
                }
                elseif ( $gameType->type === 'puzzles' ) {
                    $this->updateFragmentGamePuzzles($request, $fragment);
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
        $ageLimit = $request->input('ageLimit');
        $fragments = Auth::user()->favouriteFragments()->withCount('tags')->with('tags')
                         ->when($title, function ( $query ) use ( $title ) {
                             return $query->where('title', 'ILIKE', '%' . $title . '%');
                         })->when($type, function ( $query ) use ( $type ) {
                return $query->where('fragmentgable_type', 'ILIKE', '%' . $type . '%');
            })->when($ageLimit, function ( $query ) use ( $ageLimit ) {
                return $query->whereHas('ageLimit', function ( $query ) use ( $ageLimit ) {
                    return $query->where('id', 'LIKE', $ageLimit);
                });
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
     * Create fragment of type "Game" - "pairs" or "sequences"
     * Создать фрагмент типа "игра" подтипа "парочки" или "последовательности"
     * @param Request $request Объект запроса
     * @param User $user - Пользователь, создающий игру
     * @return Game
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    private function createFragmentGamePairsOrSequences( Request $request, User $user ): Game {
        $game = new Game();
        $gameType = GameType::where('type', $request->input('gameType'))->get()->first();
        $game->game_type_id = $gameType->id;
        $gameTask = $request->input('task') ?? $gameType->task;
        $content = $this->generateGameContentField($gameType->type, $gameTask);
        $game->content = $content;
        $game->save();
        foreach ( $request->file('content') as $index => $image ) {
            $fileName = $game['content']['gameType'] . "-" . $game->id . '-' . str_slug($user->name) . '-' . Str::random
                (10) . '.' . $image->extension();
            $content['images'][] = [
                'id'  => $index,
                'url' => $game->addMedia($image)->usingFileName($fileName)
                              ->preservingOriginal()
                              ->toMediaCollection('fragments_games', 'fragments')->getFullUrl(),
            ];
        }
        $game->content = $content;
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
     * Create fragment of type "Game" - "puzzles"
     * Создать фрагмента типа "игра" - подтип - "пазлы"
     * @param CreateFragmentRequest $request Объект запроса
     * @return Game Игра
     */
    private function createFragmentGamePuzzles( CreateFragmentRequest $request, $user ): Game {
        $game = new Game();
        $gameType = GameType::where('type', $request->input('gameType'))->get()->first();
        $game->game_type_id = $gameType->id;
        $gameTask = $request->input('task') ?? $gameType->description;
        $content = ['gameType' => $gameType->type];
        $content['task']['text'] = $gameTask;
        $content['task']['url'] = "";
        $content['image'] = [
            'id'   => 0,
            'url'  => '',
            'rows' => (int)$request->input('rows'),
            'cols' => (int)$request->input('cols'),
        ];
        $game->content = json_encode($content, JSON_UNESCAPED_UNICODE);
        $image = $request->file('content');
        $fileName = $gameType->type . '-' . $game->id . '-' . str_slug($user->name) . '-' . Str::random(10) . '.' .
            $image->extension();
        $game->addMediaFromRequest('content')->usingFileName($fileName)
             ->toMediaCollection('fragments_games', 'fragments');
        $game->save();
        $content['image']['url'] = $game->getFirstMediaUrl('fragments_games');
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
    //private function updateFragmentGamePairs( UpdateFragmentRequest $request, Fragment $fragment, User $user ): void {
    /*$user = Auth::user()
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
    $game->update(['content' => json_encode($gameContent, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);*/
    //}

    /**
     * Update fragment game - pairs
     * Обновить фрагмент типа игра - парочки
     * @param UpdateFragmentRequest $request Объект запроса
     * @param Fragment $fragment Фрагмент
     * @param User $user
     */
    private function updateFragmentGamePairs( UpdateFragmentRequest $request, Fragment $fragment, User $user ) {
        $metaImagesData = json_decode($request->input('metaImagesData'), true);
        $newImages = $request->file('content');
        $currentContent = $fragment->fragmentgable->content;
        $currentImages = $fragment->fragmentgable->content['images'];
        $game = $fragment->fragmentgable;
        // Формируем данные для новых картинок
        $updatedImages = collect($newImages)->map(function ( $image ) use ( $metaImagesData, $game, $user ) {
            $metaImage = collect($metaImagesData)->firstWhere('imageName', $image->getClientOriginalName());
            $fileName = $game->gameType->type . '-' . $game->id . '-' . str_slug($user->name) . '-' . Str::random(10) .
                '.' . $image->extension();
            return [
                'id'  => $metaImage['id'],
                'url' => $game->addMedia($image)->usingFileName($fileName)
                              ->preservingOriginal()
                              ->toMediaCollection('fragments_games', 'fragments')->getFullUrl(),
            ];
        });
        // Формируем полноценное поле content['images'], включая старые картинки
        $newUpdatedImagesData = collect($currentImages)
            ->map(function ( $currentImage ) use ( $updatedImages, $metaImagesData ) {
                if ( $updatedNewImage = $updatedImages->firstWhere('id', $currentImage['id']) ) {
                    return $updatedNewImage;
                }
                elseif ( $updatedCurrentImage = collect($metaImagesData)->firstWhere('id', $currentImage['id']) ) {
                    return $updatedCurrentImage;
                }
                return false;
            })
            ->filter(fn( $imageData ) => $imageData !== false)
            ->values();
        $currentContent['images'] = $newUpdatedImagesData;
        $newUpdatedImagesUrls = $newUpdatedImagesData->map(fn( $image ) => $image['url']);
        $newImagesGame = $game->getMedia('fragments_games')->filter(function ( $image ) use ( $newUpdatedImagesUrls ) {
            return $newUpdatedImagesUrls->contains($image->getFullUrl());
        })->values();
        $game->clearMediaCollectionExcept('fragments_games', $newImagesGame);
        $game->refresh();
        $game->update(['content' => $currentContent]);
    }

    /**
     * Update fragment game - sequences
     * Обновить фрагмент типа игра - последовательности
     * @param UpdateFragmentRequest $request Объект запроса
     * @param Fragment $fragment Фрагмент
     */
    private function updateFragmentGameSequences( UpdateFragmentRequest $request, Fragment $fragment ): void {
        $user = Auth::user();
        $game = $fragment->fragmentgable;
        $gameType = GameType::whereId($fragment->fragmentgable->game_type_id)->get()->first();
        $content = ['gameType' => $gameType->type];
        $content['task']['text'] = $request->input('task') ?? $gameType->description;
        $content['task']['mediaUrl'] = "";
        $updatedDataCollection = collect(collect($request->all())->get('content'));
        $content['images'] = $updatedDataCollection->map(function ( $value ) use ( $gameType, $game, $user ) {
            if ( gettype($value) === 'string' ) {
                return $value;
            }
            $fileName = $gameType->type . '-' . $game->id . '-' . str_slug($user->name) . '-' . Str::random(10) .
                '.' . $value->extension();
            return $game->addMedia($value)->usingFileName($fileName)->preservingOriginal()
                        ->toMediaCollection('fragments_games', 'fragments')->getFullUrl();
        });
        $updatedContentUrls = collect($content['images']);
        $updatedGameImages = $game->getMedia('fragments_games')
                                  ->filter(function ( $image ) use ( $updatedContentUrls ) {
                                      return $updatedContentUrls->contains($image->getFullUrl());
                                  })->values();
        $game->clearMediaCollectionExcept('fragments_games', $updatedGameImages);
        $game->refresh();
        $game->update(['content' => json_encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
    }

    /**
     * Update fragment game - matchmaking
     * Обновить фрагмент типа игра - ассоциации
     * @param UpdateFragmentRequest $request Объект запроса
     * @param Fragment $fragment Фрагмент
     */
    private function updateFragmentGameMatchmaking( UpdateFragmentRequest $request, Fragment $fragment ) {
        $user = $fragment->user;
        $game = $fragment->fragmentgable;
        $gameType = GameType::whereId($fragment->fragmentgable->game_type_id)->get()->first();
        $imagesCollection = collect(collect($request->only(['content']))->values()[0]);
        $content = ['gameType' => $gameType->type];
        $content['task']['text'] = $request->input('task') ?? $gameType->description;
        $content['task']['mediaUrl'] = "";
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

    /**
     * Update fragment game - puzzles
     * Обновить фрагмент типа игра - пазлы
     * @param UpdateFragmentRequest $request Объект запроса
     * @param Fragment $fragment Фрагмент
     */
    private function updateFragmentGamePuzzles( UpdateFragmentRequest $request, Fragment $fragment ): void {
        $user = Auth::user();
        $game = $fragment->fragmentgable;
        $currentContent = json_decode($game->content, true);
        if ( $image = $request->file('content') ) {
            $game->clearMediaCollection('fragments_games');
            $fileName = $currentContent['gameType'] . '-' . $game->id . '-' . str_slug($user->name) . '-' .
                Str::random(10) . '.' . $image->extension();
            $game->addMediaFromRequest('content')->usingFileName($fileName)->toMediaCollection('fragments_games');
        }
        $game->refresh();
        $newContent = ['gameType' => $currentContent['gameType']];
        $newContent['task']['text'] = $request->input('task') ?? $currentContent['task']['text'];
        $newContent['task']['mediaUrl'] = "";
        $newContent['image'] = [
            'id'   => 0,
            'url'  => $game->getFirstMediaUrl('fragments_games'),
            'rows' => (int)$request->input('rows') ?? $newContent['image']['rows'],
            'cols' => (int)$request->input('cols') ?? $newContent['image']['cols'],
        ];
        $game->update(['content' => json_encode($newContent, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
    }

    /**
     * Get layout for field content of games table
     * Получить обёртку для поля контент у игр
     * @param string $gameType
     * @param string $gameTask
     * @return array
     */
    #[ArrayShape([
        'gameType' => "string",
        "task"     => "string[]",
        "images"   => "array",
    ])] private function generateGameContentField( string $gameType, string $gameTask ): array {
        return [
            'gameType' => $gameType,
            "task"     => [
                "text"     => $gameTask,
                "mediaUrl" => "",
            ],
            "images"   => [],
        ];
    }
}
