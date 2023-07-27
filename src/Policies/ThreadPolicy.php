<?php

namespace TeamTeaTime\Forum\Policies;

use Illuminate\Database\Eloquent\Model;

class ThreadPolicy
{
    public function view($user, Model $thread): bool
    {
        return true;
    }

    public function deletePosts($user, Model $thread): bool
    {
        return true;
    }

    public function restorePosts($user, Model $thread): bool
    {
        return true;
    }

    public function rename($user, Model $thread): bool
    {
        return $user->getKey() === $thread->author_id;
    }

    public function reply($user, Model $thread): bool
    {
        return ! $thread->locked;
    }

    public function delete($user, Model $thread): bool
    {
        return $user->getKey() === $thread->author_id;
    }

    public function restore($user, Model $thread): bool
    {
        return $user->getKey() === $thread->author_id;
    }
}
