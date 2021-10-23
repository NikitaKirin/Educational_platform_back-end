<?php

namespace App\Policies;

use App\Models\Fragment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class FragmentPolicy
{
    use HandlesAuthorization;

    public function before( User $user, $operation ) {
        if ( $user->role == 'admin' )
            return true;
    }


    public function __construct() {
        //
    }

    public function viewAny( User $user ): bool {
        return true;
    }

    public function view( User $user, Fragment $fragment ): bool {
        return true;
    }

    public function create( User $user ): bool {
        return true;
    }

    public function update( User $user, Fragment $fragment ): bool {
        return $user->id == $fragment->user_id;
    }

    public function delete( User $user, Fragment $fragment ): bool {
        return $user->id == $fragment->user_id;
    }

    public function restore( User $user, Fragment $fragment ): bool {
        //
    }

    public function forceDelete( User $user, Fragment $fragment ): bool {
        //
    }
}
