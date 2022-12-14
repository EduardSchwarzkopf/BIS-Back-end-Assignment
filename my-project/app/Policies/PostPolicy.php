<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PostPolicy
{
    use HandlesAuthorization;


    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny()
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\User  $model
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view()
    {
        return true;
    }


    /**
     * Determine whether the user can create the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\User  $model
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create()
    {
        return auth()->check();
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\User  $model
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Post $model)
    {
        return $user->is_admin || (auth()->check() && $model->user_id == auth()->id());
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user)
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can view trashed items.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewTrashed(User $user)
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can force delete the model.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user)
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Post  $model
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Post $model)
    {
        return $user->is_admin || (auth()->check() && $model->user_id == auth()->id());
    }
}
