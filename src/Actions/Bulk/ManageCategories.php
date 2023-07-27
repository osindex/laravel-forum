<?php

namespace TeamTeaTime\Forum\Actions\Bulk;

use TeamTeaTime\Forum\Actions\BaseAction;
use TeamTeaTime\Forum\Factories\CategoryFactory;

class ManageCategories extends BaseAction
{
    private array $categoryData;
    protected $categoryModel = null;

    public function __construct(array $categoryData)
    {
        $this->categoryModel = CategoryFactory::model();
        $this->categoryData = $categoryData;
    }

    protected function transact()
    {
        return $this->categoryModel::rebuildTree($this->categoryData);
    }
}
