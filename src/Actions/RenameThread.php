<?php

namespace TeamTeaTime\Forum\Actions;

use Illuminate\Database\Eloquent\Model;

class RenameThread extends BaseAction
{
    private Model $thread;
    private string $title;

    public function __construct(Model $thread, string $title)
    {
        $this->thread = $thread;
        $this->title = $title;
    }

    protected function transact()
    {
        $this->thread->updateWithoutTouch([
            'title' => $this->title,
        ]);

        return $this->thread;
    }
}
