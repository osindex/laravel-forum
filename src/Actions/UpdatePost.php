<?php

namespace TeamTeaTime\Forum\Actions;

use Illuminate\Database\Eloquent\Model;

class UpdatePost extends BaseAction
{
    private Model $post;
    private string $content;

    public function __construct(Model $post, string $content)
    {
        $this->post = $post;
        $this->content = $content;
    }

    protected function transact()
    {
        $this->post->update(['content' => $this->content]);

        return $this->post;
    }
}
