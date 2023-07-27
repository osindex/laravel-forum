<?php

namespace TeamTeaTime\Forum\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use TeamTeaTime\Forum\Events\Types\BaseEvent;

class UserSearchedPosts extends BaseEvent
{
    /** @var mixed */
    public $user;

    public ?Model $category;
    public string $term;
    public LengthAwarePaginator $results;

    public function __construct($user, ?Model $category, string $term, LengthAwarePaginator $results)
    {
        $this->category = $category;
        $this->term = $term;
        $this->results = $results;
    }
}
