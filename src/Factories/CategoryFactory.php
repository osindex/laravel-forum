<?php

namespace TeamTeaTime\Forum\Factories;

use Illuminate\Database\Eloquent\Model;

class CategoryFactory
{
    /**
     * @return Model
     */
    public static function model(): Model
    {
        return app(config('forum.integration.models.category'));
    }
}
