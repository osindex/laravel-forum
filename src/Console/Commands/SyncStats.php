<?php

namespace TeamTeaTime\Forum\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

use TeamTeaTime\Forum\Factories\CategoryFactory;
use TeamTeaTime\Forum\Factories\ThreadFactory;
use TeamTeaTime\Forum\Factories\PostFactory;

class SyncStats extends Command
{
    protected $signature = 'forum:sync
                            {--model= : The model to process (Category or Thread). Omit to process both models.}
                            {--range= : The rows to process for the given model in the format skip:take. Omit to process all rows.}';

    protected $description = 'Synchronize forum category and thread statistics.';

    public function handle()
    {
        $range = $this->option('range');
        if ($range != null) {
            [$skip, $take] = explode(':', $range);
            $skip = (int) $skip;
            $take = (int) $take;
        }

        switch (strtolower($this->option('model'))) {
            case 'Category':
                $this->processCategories($skip, $take);
                break;
            case 'Thread':
                $this->processThreads($skip, $take);
                break;
            default:
                $this->processThreads();
                $this->processCategories();
        }

        $this->info('Done!');
    }

    private function processCategories(?int $skip = null, ?int $take = null)
    {
        $this->info('Processing categories...');

        $query = CategoryFactory::model()::with('threads', 'threads.posts');

        if ($skip != null) {
            $query->skip($skip);
        }

        if ($take != null) {
            $query->take($take);
        }

        $categories = $query->get();

        $bar = $this->output->createProgressBar($categories->count());
        $bar->start();

        foreach ($categories as $category) {
            $newestThreadId = $category->getNewestThreadId();
            $latestActiveThreadId = $category->getLatestActiveThreadId();

            $postCount = PostFactory::model()::whereHas('thread', function (Builder $query) use ($category) {
                $query->where('category_id', $category->id);
            })->count();

            $category->update([
                'newest_thread_id' => $newestThreadId,
                'latest_active_thread_id' => $latestActiveThreadId,
                'thread_count' => $category->threads->count(),
                'post_count' => $postCount,
            ]);

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
    }

    private function processThreads(?int $skip = null, ?int $take = null)
    {
        $this->info('Processing threads...');

        $query = ThreadFactory::model()::with('posts');

        if ($skip != null) {
            $query->skip($skip);
        }

        if ($take != null) {
            $query->take($take);
        }

        $threads = $query->get();

        $bar = $this->output->createProgressBar($threads->count());
        $bar->start();

        foreach ($threads as $thread) {
            $thread->update([
                'reply_count' => $thread->posts->count() - 1,
                'first_post_id' => $thread->posts()->orderBy('created_at', 'ASC')->first()->id,
                'last_post_id' => $thread->posts()->orderBy('created_at', 'DESC')->first()->id,
            ]);

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
    }
}
