<?php

namespace TeamTeaTime\Forum\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use TeamTeaTime\Forum\Factories\CategoryFactory;
use TeamTeaTime\Forum\Factories\ThreadFactory;
use TeamTeaTime\Forum\Factories\PostFactory;

class DeleteCategory extends BaseAction
{
    private Model $category;
    protected $categoryModel = null;
    protected $threadModel = null;
    protected $postModel = null;

    public function __construct(Model $category)
    {
        $this->categoryModel = CategoryFactory::model();
        $this->threadModel = ThreadFactory::model();
        $this->postModel = PostFactory::model();
        $this->category = $category;
    }

    protected function transact()
    {
        $categoryIdsToDelete = [];
        $threadIdsToDelete = [];
        if (!$this->category->isEmpty()) {
            $descendantIds = $this->category->descendants->pluck('id')->toArray();
            $categoryIdsToDelete = $descendantIds;
            $threadIdsToDelete = $this->threadModel::whereIn('category_id', $descendantIds)->withTrashed()->pluck('id')->toArray();
        }

        $categoryIdsToDelete[] = $this->category->id;
        $threadIdsToDelete = array_merge($threadIdsToDelete, $this->category->threads()->withTrashed()->pluck('id')->toArray());

        $this->postModel::whereIn('thread_id', $threadIdsToDelete)->withTrashed()->forceDelete();
        DB::table($this->threadModel::READERS_TABLE)->whereIn('thread_id', $threadIdsToDelete)->delete();
        $this->threadModel::whereIn('id', $threadIdsToDelete)->withTrashed()->forceDelete();

        return $this->categoryModel::whereIn('id', $categoryIdsToDelete)->delete();
    }
}
