<?php

namespace TeamTeaTime\Forum\Actions\Bulk;

use Illuminate\Support\Facades\DB;
use TeamTeaTime\Forum\Actions\BaseAction;
use TeamTeaTime\Forum\Factories\ThreadFactory;

class DeleteThreads extends BaseAction
{
    private array $threadIds;
    private bool $includeTrashed;
    private bool $permaDelete;
    protected $threadModel = null;

    public function __construct(array $threadIds, bool $includeTrashed, bool $permaDelete = false)
    {
        $this->threadModel = ThreadFactory::model();
        $this->threadIds = $threadIds;
        $this->includeTrashed = $includeTrashed;
        $this->permaDelete = $permaDelete;
    }

    protected function transact()
    {
        $query = $this->threadModel::whereIn('id', $this->threadIds);

        if ($this->includeTrashed) {
            $threads = $query->withTrashed()->get();

            // Return early if this is a soft-delete and the selected threads are already trashed,
            // or there are no valid threads in the selection
            if (!$this->permaDelete && $threads->whereNull($this->threadModel::DELETED_AT)->count() == 0) {
                return null;
            }
        } else {
            $threads = $query->get();

            // Return early if there are no valid threads in the selection
            if ($threads->count() == 0) {
                return null;
            }
        }

        // Use the raw query builder to prevent touching updated_at
        $query = DB::table($this->threadModel::getTableName())->whereIn('id', $this->threadIds);

        if ($this->permaDelete) {
            $rowsAffected = $query->delete();

            // Drop readers for the removed threads
            DB::table($this->threadModel::READERS_TABLE)->whereIn('thread_id', $this->threadIds)->delete();
        } else {
            $rowsAffected = $query->whereNull($this->threadModel::DELETED_AT)->update([$this->threadModel::DELETED_AT => DB::raw('now()')]);
        }

        $threadsByCategory = $threads->groupBy('category_id');
        foreach ($threadsByCategory as $categoryThreads) {
            // Count only non-deleted threads for changes to category stats since soft-deleted threads
            // are already represented
            $threadCount = $categoryThreads->whereNull($this->threadModel::DELETED_AT)->count();

            // Sum of reply counts + thread count = total posts
            $postCount = $categoryThreads->whereNull($this->threadModel::DELETED_AT)->sum('reply_count') + $threadCount;

            $category = $categoryThreads->first()->category;

            $updates = [
                'newest_thread_id' => $category->getNewestThreadId(),
                'latest_active_thread_id' => $category->getLatestActiveThreadId(),
            ];

            if ($threadCount > 0) {
                $updates['thread_count'] = DB::raw("thread_count - {$threadCount}");
            }
            if ($postCount > 0) {
                $updates['post_count'] = DB::raw("post_count - {$postCount}");
            }

            $category->update($updates);
        }

        return $threads;
    }
}
