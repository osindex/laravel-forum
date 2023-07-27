<?php

namespace TeamTeaTime\Forum\Actions;

use Illuminate\Database\Eloquent\Model;

class UnpinThread extends BaseAction
{
    private Model $thread;

    public function __construct(Model $thread)
    {
        $this->thread = $thread;
    }

    protected function transact()
    {
        if (! $this->thread->pinned) {
            return null;
        }

        $this->thread->updateWithoutTouch([
            'pinned' => false,
        ]);

        return $this->thread;
    }
}
