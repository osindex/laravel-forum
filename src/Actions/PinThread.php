<?php

namespace TeamTeaTime\Forum\Actions;

use Illuminate\Database\Eloquent\Model;

class PinThread extends BaseAction
{
    private Model $thread;

    public function __construct(Model $thread)
    {
        $this->thread = $thread;
    }

    protected function transact()
    {
        if ($this->thread->pinned) {
            return null;
        }

        $this->thread->updateWithoutTouch([
            'pinned' => true,
        ]);

        return $this->thread;
    }
}
