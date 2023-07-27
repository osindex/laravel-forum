<?php

namespace TeamTeaTime\Forum\Actions;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use TeamTeaTime\Forum\Factories\PostFactory;

class SearchPosts extends BaseAction
{
    private ?Model $category;
    private string $term;
    protected $postModel = null;
    public function __construct(?Model $category, string $term)
    {
        $this->postModel = PostFactory::model();
        $this->category = $category;
        $this->term = $term;
    }

    protected function transact()
    {
        $posts = $this->postModel::sortBy(request()->get('sort', '-id'))
            ->status(request()->get('status', '-1'))
            ->with('thread', 'thread.category')
            ->when($this->category, function (Builder $query) {
                $query->whereHas('thread.category', function (Builder $query) {
                    $query->where('id', $this->category->id);
                });
            })
            ->where('content', 'like', "%{$this->term}%")
            ->paginate();

        $threadIds = $posts->getCollection()->pluck('thread')->filter(function ($thread) {
            return !$thread->category->is_private || Gate::allows('view', $thread->category) && Gate::allows('view', $thread);
        })->pluck('id')->unique();

        $posts->setCollection($posts->getCollection()->filter(function ($post) use ($threadIds) {
            return $threadIds->contains($post->thread->id);
        }));

        return $posts;
    }
}
