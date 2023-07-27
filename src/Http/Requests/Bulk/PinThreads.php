<?php

namespace TeamTeaTime\Forum\Http\Requests\Bulk;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Http\FormRequest;
use TeamTeaTime\Forum\Actions\Bulk\PinThreads as Action;
use TeamTeaTime\Forum\Events\UserBulkPinnedThreads;
use TeamTeaTime\Forum\Factories\CategoryFactory;
use TeamTeaTime\Forum\Factories\ThreadFactory;
use TeamTeaTime\Forum\Http\Requests\Traits\AuthorizesAfterValidation;
use TeamTeaTime\Forum\Interfaces\FulfillableRequest;

class PinThreads extends FormRequest implements FulfillableRequest
{
    use AuthorizesAfterValidation;

    public function rules(): array
    {
        return [
            'threads' => ['required', 'array'],
        ];
    }

    public function authorizeValidated(): bool
    {
        $categories = $this->categories();
        foreach ($categories as $category) {
            if (! $this->user()->can('pinThreads', $category)) {
                return false;
            }
        }

        return true;
    }

    public function fulfill()
    {
        $action = new Action($this->validated()['threads'], $this->user()->can('viewTrashedThreads'));
        $threads = $action->execute();

        if ($threads !== null) {
            UserBulkPinnedThreads::dispatch($this->user(), $threads);
        }

        return $threads;
    }

    protected function categories(): Collection
    {
        $query = ThreadFactory::model()::whereIn('id', $this->validated()['threads']);

        if ($this->user()->can('viewTrashedThreads')) {
            $query = $query->withTrashed();
        }

        $categoryIds = $query->select('category_id')->distinct()->pluck('category_id');

        return CategoryFactory::model()::whereIn('id', $categoryIds)->get();
    }
}
