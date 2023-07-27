<?php

namespace TeamTeaTime\Forum\Policies;

use Illuminate\Database\Eloquent\Model;

class CategoryPolicy
{
    public function createThreads($user, Model $category): bool
    {
        return true;
    }

    public function manageThreads($user, Model $category): bool
    {
        return $this->deleteThreads($user, $category) ||
               $this->restoreThreads($user, $category) ||
               $this->moveThreadsFrom($user, $category) ||
               $this->lockThreads($user, $category) ||
               $this->pinThreads($user, $category);
    }

    public function deleteThreads($user, Model $category): bool
    {
        return true;
    }

    public function restoreThreads($user, Model $category): bool
    {
        return true;
    }

    public function enableThreads($user, Model $category): bool
    {
        return false;
    }

    public function moveThreadsFrom($user, Model $category): bool
    {
        return false;
    }

    public function moveThreadsTo($user, Model $category): bool
    {
        return false;
    }

    public function lockThreads($user, Model $category): bool
    {
        return false;
    }

    public function pinThreads($user, Model $category): bool
    {
        return false;
    }

    public function markThreadsAsRead($user, Model $category): bool
    {
        return true;
    }

    public function view($user, Model $category): bool
    {
        return true;
    }

    public function delete($user, Model $category): bool
    {
        return false;
    }
}
