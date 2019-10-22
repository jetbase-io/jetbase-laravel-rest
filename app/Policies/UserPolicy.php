<?php

namespace App\Policies;

use App\Model\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function before(User $user, $ability)
    {
        if ($user->isAdmin()) {
            return true;
        }
    }

    /**
     * Determine whether the user can view any models.
     *
     * @param \App\Model\User $user
     *
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param \App\Model\User $user
     * @param \App\Model\User $model
     *
     * @return mixed
     */
    public function view(User $user, User $model)
    {
        //
    }

    /**
     * Determine whether the user can create models.
     *
     * @param \App\Model\User $user
     *
     * @return mixed
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param \App\Model\User $user
     * @param \App\Model\User $model
     *
     * @return mixed
     */
    public function update(User $user, User $model)
    {
        return $user->id === $model->id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param \App\Model\User $user
     * @param \App\Model\User $model
     *
     * @return mixed
     */
    public function delete(User $user, User $model)
    {
        return $user->id === $model->id;
    }

    public function changePassword(User $user, User $model)
    {
        return $user->id === $model->id;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param \App\Model\User $user
     * @param \App\Model\User $model
     *
     * @return mixed
     */
    public function restore(User $user, User $model)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param \App\Model\User $user
     * @param \App\Model\User $model
     *
     * @return mixed
     */
    public function forceDelete(User $user, User $model)
    {
        //
    }

    /**
     * Determine whether the user can assign role to the model.
     *
     * @param \App\Model\User $user
     * @param \App\Model\User $model
     *
     * @return bool
     */
    public function assignRole(User $user, User $model)
    {
        return false;
    }

    /**
     * User can change role for other user?
     *
     * @param \App\Model\User $user
     * @param \App\Model\User $model
     *
     * @return bool
     */
    public function changeRole(User $user, User $model)
    {
        return false;
    }
}
