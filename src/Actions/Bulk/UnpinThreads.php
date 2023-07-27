<?php

namespace TeamTeaTime\Forum\Actions\Bulk;

use Illuminate\Support\Facades\DB;
use TeamTeaTime\Forum\Actions\BaseAction;
use TeamTeaTime\Forum\Factories\ThreadFactory;

class UnpinThreads extends BaseAction
{
    private array $threadIds;
    private bool $includeTrashed;
    protected $threadModel = null;

    public function __construct(array $threadIds, bool $includeTrashed)
    {
        $this->threadModel = ThreadFactory::model();
        $this->threadIds = $threadIds;
        $this->includeTrashed = $includeTrashed;
    }

    protected function transact()
    {
        $query = DB::table($this->threadModel::getTableName())
            ->whereIn('id', $this->threadIds)
            ->where(['pinned' => true]);

        if (!$this->includeTrashed) {
            $query = $query->whereNull($this->threadModel::DELETED_AT);
        }

        $threads = $query->get();

        // Return early if there are no eligible threads in the selection
        if ($threads->count() == 0) {
            return null;
        }

        $query->update(['pinned' => false]);

        return $threads;
    }
}
