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
     * @param Request $request Объект запроса
     * @return Article
     */
    private function createFragmentArticle( Request $request ): Article {
        $fragmentData = new Article(['content' => $request->input('content')]);
        $fragmentData->save();
        return $fragmentData;
    }

    /**
     * Create fragment of type "video"
     * Создать фрагмент типа "Видео"
     * @param Request $request Объект запроса
     * @return Video
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    private function createFragmentVideo( Request $request ): Video {
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
     * @param Request $request Объект запроса
     * @return Image
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    private function createFragmentImage( Request $request ): Image {
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
        $fragmentData = new Game();
        $fragmentData->type = $request->input('gameType');
        $content = [];
        $fragmentData->content = json_encode($content);
        $fragmentData->save();
        $fragmentData->addMultipleMediaFromRequest(['content'])
                     ->each(function ( $file_adder ) use ( $user, $fragmentData ) {
                         $file_adder->usingFileName("$user->name-" . "$fragmentData->type-" . Str::random('5') . '.jpg')
                                    ->toMediaCollection('fragments_games', 'fragments');
                     });
        $dataImages = $fragmentData->getMedia('fragments_games');
        foreach ( $dataImages as $dataImage ) {
            $content[] = $dataImage->getFullUrl();
        }
        $fragmentData->content = json_encode($content, JSON_UNESCAPED_SLASHES);
        $fragmentData->save();
        return $fragmentData;
    }

    /**
     * Update fragment of types: video or image
     * Обновить фрагмент типа видеоролик или изображение
     * @param Request $request
     * @param Fragment $fragment
     */
    private function updateFragmentVideoOrImage( Request $request, Fragment $fragment ): void {
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
     * @param Request $request
     * @param Fragment $fragment
     */
    private function updateFragmentArticle( Request $request, Fragment $fragment ): void {
        $request->validate(['content' => 'string'], ['string' => 'На вход ожидалась строка']);
        $fragment->update($request->only('title'));
        $fragment->fragmentgable->update($request->only('content'));
    }
}
