<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('posts:refresh')]
#[Description('Re-run BlogSeeder to update post content (uses updateOrCreate, safe to run)')]
class UpdateBlogContent extends Command
{
    public function handle(): void
    {
        $this->info('Refreshing blog post content from seeder...');
        $this->call('db:seed', ['--class' => 'Database\\Seeders\\BlogSeeder', '--force' => true]);
        $this->info('Done! Blog posts updated.');
    }
}
