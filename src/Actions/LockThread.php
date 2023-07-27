<?php

namespace TeamTeaTime\Forum\Actions;

use Illuminate\Database\Eloquent\Model;

class LockThread extends BaseAction
{
    private Model $thread;

    public function __construct(Model $thread)
    {
        $this->thread = $thread;
    }

    protected function transact()
    {
        if ($this->thread->locked) {
            return null;
        }

        $this->thread->updateWithoutTouch([
            'locked' => true,
        ]);

        return $this->thread;
    }
}
