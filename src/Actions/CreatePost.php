<?php

namespace TeamTeaTime\Forum\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use TeamTeaTime\Forum\Factories\PostFactory;
use TeamTeaTime\Forum\Factories\ThreadFactory;

class CreatePost extends BaseAction
{
    protected $threadModel = null;
    protected $postModel = null;

    private Model $thread;
    private ?Model $parent;
    private Model $author;
    private string $content;

    public function __construct(Model $thread, ?Model $parent, Model $author, string $content)
    {
        $this->threadModel = ThreadFactory::model();
        $this->postModel = PostFactory::model();

        $this->thread = $thread;
        $this->parent = $parent;
        $this->author = $author;
        $this->content = $content;
    }

    protected function transact()
    {
        $post = $this->thread->posts()->create([
            'post_id' => $this->parent === null ? null : $this->parent->id,
            'author_id' => $this->author->getKey(),
            'sequence' => $this->thread->posts->count() + 1,
            'content' => $this->content,
        ]);
        $update = [
            'last_post_id' => $post->id,
            'reply_count' => DB::raw('reply_count + 1'),
        ];
        if (!$this->thread->first_post_id) {
            $update['first_post_id'] = $post->id;
        }
        $this->thread->update($update);

        $this->thread->category->updateWithoutTouch([
            'latest_active_thread_id' => $this->thread->id,
            'post_count' => DB::raw('post_count + 1'),
        ]);

        return $post;
    }
}
