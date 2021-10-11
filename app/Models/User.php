<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Laravel\Passport\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class User extends Authenticatable implements HasMedia
{
    use HasApiTokens, HasFactory, Notifiable, InteractsWithMedia;

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
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // Мутатор для хеширования пароля пользователя
    public function setPasswordAttribute( $value ) {
        $this->attributes['password'] = Hash::make($value);
    }

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
    public function hasAvatar(): bool {
        $avatar = Auth::user()->getFirstMediaUrl('user_avatars');
        if ( empty($avatar) )
            return false;
        return true;
    }

    //Получаем аватар пользователя
    public function getAvatar() {
        if ( $this->hasAvatar() ) {
            $avatar = Auth::user()->getFirstMediaUrl('user_avatars');
            return $avatar;
        }
        return null;
    }
}
