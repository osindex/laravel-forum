<?php

namespace TeamTeaTime\Forum\Events\Types;

use Illuminate\Database\Eloquent\Model;

class ThreadEvent extends BaseEvent
{
    /** @var mixed */
    public $user;

    public Model $thread;

    public function __construct($user, Model $thread)
    {
        $this->user = $user;
        $this->thread = $thread;
    }
}
