<?php

namespace TeamTeaTime\Forum\Factories;

class PostFactory
{
    /**
     * @return Model
     */
    public static function model()
    {
        return app(config('forum.integration.models.post'));
    }
}
