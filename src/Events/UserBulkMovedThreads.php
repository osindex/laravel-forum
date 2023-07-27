<?php

namespace TeamTeaTime\Forum\Events;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection as SupportCollection;
use TeamTeaTime\Forum\Events\Types\CollectionEvent;

class UserBulkMovedThreads extends CollectionEvent
{
    public Collection $sourceCategories;
    public Model $destinationCategory;

    public function __construct($user, SupportCollection $threads, Collection $sourceCategories, Model $destinationCategory)
    {
        parent::__construct($user, $threads);

        $this->sourceCategories = $sourceCategories;
        $this->destinationCategory = $destinationCategory;
    }
}
