<?php

namespace TeamTeaTime\Forum\Events\Types;

use Illuminate\Database\Eloquent\Model;

class PostEvent extends BaseEvent
{
    /** @var mixed */
    public $user;

    public Model $post;

    public function __construct($user, Model $post)
    {
        $this->user = $user;
        $this->post = $post;
    }
}
