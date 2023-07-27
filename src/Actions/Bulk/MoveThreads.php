<?php

namespace TeamTeaTime\Forum\Actions\Bulk;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use TeamTeaTime\Forum\Actions\BaseAction;
use TeamTeaTime\Forum\Factories\CategoryFactory;
use TeamTeaTime\Forum\Factories\ThreadFactory;

class MoveThreads extends BaseAction
{
    private array $threadIds;
    private Model $destinationCategory;
    private bool $includeTrashed;
    protected $categoryModel = null;
    protected $threadModel = null;

    public function __construct(array $threadIds, Model $destinationCategory, bool $includeTrashed)
    {
        $this->categoryModel = CategoryFactory::model();
        $this->threadModel = ThreadFactory::model();
        $this->threadIds = $threadIds;
        $this->destinationCategory = $destinationCategory;
        $this->includeTrashed = $includeTrashed;
    }

    protected function transact()
    {
        // Don't include threads that are already in the destination category
        $query = DB::table($this->threadModel::getTableName())->where('category_id', '!=', $this->destinationCategory->id)->whereIn('id', $this->threadIds);

        $threads = $this->includeTrashed
            ? $query->get()
            : $query->whereNull('deleted_at')->get();

        // Return early if there are no eligible threads in the selection
        if ($threads->count() == 0) {
            return null;
        }

        $threadsByCategory = $threads->groupBy('category_id');
        $sourceCategories = $this->categoryModel::whereIn('id', $threads->pluck('category_id'))->get();
        $destinationCategory = $this->destinationCategory;

        $query->update(['category_id' => $destinationCategory->id]);

        $seen = [];
        foreach ($sourceCategories as $category) {
            if (in_array($category->id, $seen)) {
                continue;
            }

            $categoryThreads = $threadsByCategory->get($category->id);
            $threadCount = $categoryThreads->count();
            $postCount = $threadCount + $categoryThreads->sum('reply_count');
            $category->updateWithoutTouch([
                'newest_thread_id' => $category->getNewestThreadId(),
                'latest_active_thread_id' => $category->getLatestActiveThreadId(),
                'thread_count' => DB::raw("thread_count - {$threadCount}"),
                'post_count' => DB::raw("post_count - {$postCount}"),
            ]);

            $seen[] = $category->id;
        }

        $threadCount = $threads->count();
        $postCount = $threads->count() + $threads->sum('reply_count');
        $destinationCategory->updateWithoutTouch([
            'newest_thread_id' => max($threads->max('id'), $destinationCategory->newest_thread_id),
            'latest_active_thread_id' => $destinationCategory->getLatestActiveThreadId(),
            'thread_count' => DB::raw("thread_count + {$threadCount}"),
            'post_count' => DB::raw("post_count + {$postCount}"),
        ]);

        return $threads;
    }
}
