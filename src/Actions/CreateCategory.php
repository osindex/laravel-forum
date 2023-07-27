<?php

namespace TeamTeaTime\Forum\Actions;

use TeamTeaTime\Forum\Factories\CategoryFactory;

class CreateCategory extends BaseAction
{
    protected $model = null;

    private string $title;
    private string $description;
    private string $color;
    private bool $acceptsThreads;
    private bool $isPrivate;
    private int $parentId;

    public function __construct(string $title, string $description, string $color, bool $acceptsThreads = true, bool $isPrivate = false, bool $parentId = null)
    {
        $this->model = CategoryFactory::model();

        $this->title = $title;
        $this->description = $description;
        $this->color = $color;
        $this->acceptsThreads = $acceptsThreads;
        $this->isPrivate = $isPrivate;
        $this->parentId = $parentId;
    }

    protected function transact()
    {
        return $this->model::create([
            'title' => $this->title,
            'description' => $this->description,
            'color' => $this->color,
            'accepts_threads' => $this->acceptsThreads,
            'is_private' => $this->isPrivate,
            'parent_id' => $this->parentId,
        ]);
    }
}
