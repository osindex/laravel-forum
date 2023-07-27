<?php

namespace TeamTeaTime\Forum\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;
use TeamTeaTime\Forum\Support\CategoryPrivacy;
use TeamTeaTime\Forum\Factories\ThreadFactory;

class MarkThreadsAsRead extends BaseAction
{
    private User $user;
    private ?Model $category;
    protected $threadModel = null;
    public function __construct(User $user, ?Model $category)
    {
        $this->threadModel = ThreadFactory::model();
        $this->user = $user;
        $this->category = $category;
    }

    protected function transact()
    {
        $threads = $this->threadModel::recent();

        if ($this->category !== null) {
            $threads = $threads->where('category_id', $this->category->id);
        }

        $accessibleCategoryIds = CategoryPrivacy::getFilteredFor($this->user)->keys();

        $threads = $threads->get()->filter(function ($thread) use ($accessibleCategoryIds) {
            // @TODO: handle authorization check outside of action?
            return $thread->userReadStatus != null
                && (!$thread->category->is_private || ($accessibleCategoryIds->contains($thread->category_id) && $this->user->can('view', $thread)));
        });

        foreach ($threads as $thread) {
            $thread->markAsRead($this->user->getKey());
        }

        return $threads;
    }
}
