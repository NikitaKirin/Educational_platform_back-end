<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\HasApiTokens;
use Orchid\Metrics\Chartable;
use Orchid\Platform\Models\User as Authenticatable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class User extends Authenticatable implements HasMedia
{
    use HasApiTokens, HasFactory, Notifiable, InteractsWithMedia, CanResetPassword, Chartable;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'birthday',
        'role',
        'blocked_at',
        'permissions',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'permissions',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'permissions'          => 'array',
        'email_verified_at'    => 'datetime',
    ];

    /**
     * The attributes for which you can use filters in url.
     *
     * @var array
     */
    protected $allowedFilters = [
        'id',
        'name',
        'email',
        'permissions',
    ];

    /**
     * The attributes for which can use sort in url.
     *
     * @var array
     */
    protected $allowedSorts = [
        'id',
        'name',
        'email',
        'updated_at',
        'created_at',
    ];

    // Мутатор для хеширования пароля пользователя
    /*public function setPasswordAttribute( $value ) {
        $this->attributes['password'] = Hash::make($value);
    }*/

    //Акцессор для преобразования формата даты рождения пользователя
    public function getBirthdayAttribute( $value ): ?string {
        if ( isset($value) ) {
            return Carbon::parse($value)->format('d.m.Y');
        }

        return null;
    }

    //Мутатор для преобразования формата даты рождения пользователя
    public function setBirthdayAttribute( $value ) {
        if ( isset($value) )
            $this->attributes['birthday'] = Carbon::parse($value)->toDateString();
    }

    //Проверяем наличие у пользователя аватара
    public static function hasAvatar( User $user ): bool {
        $avatar = $user->getFirstMediaUrl('user_avatars');
        if ( empty($avatar) )
            return false;
        return true;
    }

    //Получаем аватар пользователя
    public static function getAvatar( User $user ): ?string {
        if ( $user->hasAvatar($user) ) {
            return $user->getFirstMediaUrl('user_avatars');
        }
        return null;
    }

    //Акцессор для преобразования формата поля блокировки пользователя
    public function getBlockedAtAttribute( $value ): ?string {
        if ( isset($value) )
            return Carbon::parse($value)->format('d.m.Y / H:i:s');

        return null;
    }

    // Устанавливаем прямую связь "один со многим" с таблицей "fragments"
    public function fragments(): \Illuminate\Database\Eloquent\Relations\HasMany {
        return $this->hasMany(Fragment::class);
    }

    // Устанавливаем связь многие ко многим через промежуточные таблицу "fragment_user" с сущностью "fragment"
    // реализуем функцию добавить фрагмент в избранное
    public function favouriteFragments(): \Illuminate\Database\Eloquent\Relations\BelongsToMany {
        return $this->belongsToMany(Fragment::class, 'fragment_user');
    }

    // Устанавливаем прямую связь "один со многим" с таблицей "lessons"
    public function lessons(): \Illuminate\Database\Eloquent\Relations\HasMany {
        return $this->hasMany(Lesson::class);
    }

    // Устанавливаем связь многие ко многим через промежуточные таблицу "lesson_user" с сущностью "lesson"
    // реализуем функцию добавить урок в избранное
    public function favouriteLessons(): \Illuminate\Database\Eloquent\Relations\BelongsToMany {
        return $this->belongsToMany(Lesson::class, 'lesson_user');
    }
}
