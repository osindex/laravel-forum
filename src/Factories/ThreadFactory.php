<?php

namespace TeamTeaTime\Forum\Factories;

class ThreadFactory
{
    /**
     * @return Model
     */
    public static function model()
    {
        return app(config('forum.integration.models.thread'));
    }
}
