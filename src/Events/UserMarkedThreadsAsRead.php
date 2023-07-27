<?php

namespace TeamTeaTime\Forum\Events;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use TeamTeaTime\Forum\Events\Types\CollectionEvent;

class UserMarkedThreadsAsRead extends CollectionEvent
{
    public ?Model $category;

    public function __construct($user, ?Model $category, Collection $threads)
    {
        parent::__construct($user, $threads);

        $this->category = $category;
    }
}
