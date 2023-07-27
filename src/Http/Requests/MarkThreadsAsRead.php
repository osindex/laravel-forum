<?php

namespace TeamTeaTime\Forum\Http\Requests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use TeamTeaTime\Forum\Actions\MarkThreadsAsRead as Action;
use TeamTeaTime\Forum\Events\UserMarkedThreadsAsRead;
use TeamTeaTime\Forum\Factories\CategoryFactory;
use TeamTeaTime\Forum\Http\Requests\Traits\AuthorizesAfterValidation;
use TeamTeaTime\Forum\Interfaces\FulfillableRequest;

class MarkThreadsAsRead extends FormRequest implements FulfillableRequest
{
    use AuthorizesAfterValidation;

    private ?Model $category;

    public function rules(): array
    {
        return [
            'category_id' => ['int', 'exists:forum_categories,id'],
        ];
    }

    public function authorizeValidated(): bool
    {
        $category = $this->category();

        if ($category !== null && ! $category->isAccessibleTo($this->user())) {
            return false;
        }

        return $this->user()->can('markThreadsAsRead', $category);
    }

    public function fulfill()
    {
        $category = $this->category();

        $action = new Action($this->user(), $category);
        $threads = $action->execute();

        UserMarkedThreadsAsRead::dispatch($this->user(), $category, $threads);

        return $category;
    }

    private function category()
    {
        if (! isset($this->category)) {
            $this->category = isset($this->validated()['category_id']) ? CategoryFactory::model()::find($this->validated()['category_id']) : null;
        }

        return $this->category;
    }
}
