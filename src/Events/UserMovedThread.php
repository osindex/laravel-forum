<?php

namespace TeamTeaTime\Forum\Events;

use Illuminate\Database\Eloquent\Model;
use TeamTeaTime\Forum\Events\Types\ThreadEvent;

class UserMovedThread extends ThreadEvent
{
    public Model $destinationCategory;

    public function __construct($user, Model $thread, Model $destinationCategory)
    {
        parent::__construct($user, $thread);

        $this->destinationCategory = $destinationCategory;
    }
}
