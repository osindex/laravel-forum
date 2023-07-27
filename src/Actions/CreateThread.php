<?php

namespace TeamTeaTime\Forum\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use TeamTeaTime\Forum\Factories\CategoryFactory;
use TeamTeaTime\Forum\Factories\ThreadFactory;

class CreateThread extends BaseAction
{
    protected $categoryModel = null;
    protected $threadModel = null;

    private Model $category;
    private Model $author;
    private string $title;
    private string $content;
    private array $images;

    public function __construct(Model $category, Model $author, string $title, string $content, array $images)
    {
        $this->categoryModel = CategoryFactory::model();
        $this->threadModel = ThreadFactory::model();

        $this->category = $category;
        $this->author = $author;
        $this->title = $title;
        $this->content = $content;
        $this->images = $images;
    }

    protected function transact()
    {
        $thread = $this->threadModel::create([
            'author_id' => $this->author->getKey(),
            'category_id' => $this->category->id,
            'title' => $this->title,
            'images' => $this->images,
        ]);

        $post = $thread->posts()->create([
            'author_id' => $this->author->getKey(),
            'content' => $this->content,
            'sequence' => 1,
        ]);

        $thread->update([
            'first_post_id' => $post->id,
            'last_post_id' => $post->id,
        ]);

        $thread->category->updateWithoutTouch([
            'newest_thread_id' => $thread->id,
            'latest_active_thread_id' => $thread->id,
            'thread_count' => DB::raw('thread_count + 1'),
            'post_count' => DB::raw('post_count + 1'),
        ]);

        return $thread;
    }
}
