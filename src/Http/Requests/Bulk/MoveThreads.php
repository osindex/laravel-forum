<?php

namespace TeamTeaTime\Forum\Http\Requests\Bulk;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use TeamTeaTime\Forum\Actions\Bulk\MoveThreads as Action;
use TeamTeaTime\Forum\Events\UserBulkMovedThreads;
use TeamTeaTime\Forum\Factories\CategoryFactory;
use TeamTeaTime\Forum\Factories\ThreadFactory;
use TeamTeaTime\Forum\Http\Requests\Traits\AuthorizesAfterValidation;
use TeamTeaTime\Forum\Interfaces\FulfillableRequest;
use TeamTeaTime\Forum\Support\CategoryPrivacy;

class MoveThreads extends FormRequest implements FulfillableRequest
{
    use AuthorizesAfterValidation;

    private ?Collection $sourceCategories = null;
    private ?Model $destinationCategory = null;

    public function rules(): array
    {
        return [
            'threads' => ['required', 'array'],
            'category_id' => ['required', 'int', 'exists:forum_categories,id'],
        ];
    }

    public function authorizeValidated(): bool
    {
        $destinationCategory = $this->getDestinationCategory();

        $accessibleCategoryIds = CategoryPrivacy::getFilteredFor($this->user())->keys();

        if (!($accessibleCategoryIds->contains($destinationCategory->id) || $this->user()->can('moveThreadsTo', $destinationCategory))) {
            return false;
        }

        foreach ($this->getSourceCategories() as $category) {
            if (!($accessibleCategoryIds->contains($category->id) || $this->user()->can('moveThreadsFrom', $category))) {
                return false;
            }
        }

        return true;
    }

    public function fulfill()
    {
        $action = new Action(
            $this->validated()['threads'],
            $this->getDestinationCategory(),
            $this->user()->can('viewTrashedThreads')
        );
        $threads = $action->execute();

        if ($threads !== null) {
            UserBulkMovedThreads::dispatch($this->user(), $threads, $this->getSourceCategories(), $this->getDestinationCategory());
        }

        return $threads;
    }

    private function getSourceCategories()
    {
        if (!$this->sourceCategories) {
            $query = ThreadFactory::model()::select('category_id')
                ->distinct()
                ->where('category_id', '!=', $this->validated()['category_id'])
                ->whereIn('id', $this->validated()['threads']);

            if (!$this->user()->can('viewTrashedThreads')) {
                $query = $query->whereNull(ThreadFactory::model()::DELETED_AT);
            }

            $this->sourceCategories = CategoryFactory::model()::whereIn('id', $query->get()->pluck('category_id'))->get();
        }

        return $this->sourceCategories;
    }

    private function getDestinationCategory()
    {
        if ($this->destinationCategory == null) {
            $this->destinationCategory = CategoryFactory::model()::find($this->validated()['category_id']);
        }

        return $this->destinationCategory;
    }
}
