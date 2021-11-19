<?php

namespace App\Policies;

use App\Models\Lesson;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LessonPolicy
{
    use HandlesAuthorization;

    public function before( User $user, $operation ): ?bool {
        if ( $operation != 'create' && $user->role == 'admin' )
            return true;
        return null;
    }

    public function __construct() {
        //
    }

    public function viewAny( User $user ): bool {
        //
    }

    public function view( User $user, Lesson $lesson ): bool {
        //
    }

    public function create( User $user ): bool {
        if ( $user == 'creator' || 'student' )
            return true;
        return false;
    }

    public function update( User $user, Lesson $lesson ): bool {
        return $user->id == $lesson->user_id;
    }

    public function delete( User $user, Lesson $lesson ): bool {
        return $user->id == $lesson->user_id;
    }

    public function restore( User $user, Lesson $lesson ): bool {
        //
    }

    public function forceDelete( User $user, Lesson $lesson ): bool {
        //
    }
}
