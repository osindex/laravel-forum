<?php

namespace TeamTeaTime\Forum\Policies;

use Illuminate\Database\Eloquent\Model;

class PostPolicy
{
    public function edit($user, Model $post): bool
    {
        return $user->getKey() === $post->author_id;
    }

    public function delete($user, Model $post): bool
    {
        return $user->getKey() === $post->author_id;
    }

    public function restore($user, Model $post): bool
    {
        return $user->getKey() === $post->author_id;
    }
}
