<?php

namespace TeamTeaTime\Forum\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RestoreThread extends BaseAction
{
    private Model $thread;

    public function __construct(Model $thread)
    {
        $this->thread = $thread;
    }

    protected function transact()
    {
        if (! $this->thread->trashed()) {
            return null;
        }

        $this->thread->setTouchedRelations([])->restoreWithoutTouch();

        $category = $this->thread->category;
        $category->update([
            'newest_thread_id' => max($this->thread->id, $category->newest_thread_id),
            'latest_active_thread_id' => $category->getLatestActiveThreadId(),
            'thread_count' => DB::raw('thread_count + 1'),
            'post_count' => DB::raw("post_count + {$this->thread->postCount}"),
        ]);

        return $this->thread;
    }
}
