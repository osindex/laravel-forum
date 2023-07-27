<?php

namespace TeamTeaTime\Forum\Events\Types;

use Illuminate\Database\Eloquent\Model;

class CategoryEvent extends BaseEvent
{
    /** @var mixed */
    public $user;

    public Model $category;

    public function __construct($user, Model $category)
    {
        $this->user = $user;
        $this->category = $category;
    }
}
