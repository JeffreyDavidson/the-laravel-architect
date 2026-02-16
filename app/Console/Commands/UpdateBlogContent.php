<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class UpdateBlogContent extends Command
{
    protected $signature = 'posts:refresh';
    protected $description = 'Re-run BlogSeeder to update post content (uses updateOrCreate, safe to run)';

    public function handle(): void
    {
        $this->info('Refreshing blog post content from seeder...');
        $this->call('db:seed', ['--class' => 'Database\\Seeders\\BlogSeeder', '--force' => true]);
        $this->info('Done! Blog posts updated.');
    }
}
