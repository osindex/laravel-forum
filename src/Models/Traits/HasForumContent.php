<?php

namespace TeamTeaTime\Forum\Models\Traits;

use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasForumContent
{
    public function threads(): HasMany
    {
        return $this->hasMany(config('forum.integration.models.thread'), 'author_id');
    }

    public function posts(): HasMany
    {
        return $this->hasMany(config('forum.integration.models.post'), 'author_id');
    }
}
