<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('posts:refresh')]
#[Description('Re-run BlogSeeder to update post content (uses updateOrCreate, safe to run)')]
class UpdateBlogContent extends Command
{
    public function handle(): int
    {
        $this->info('Refreshing blog post content from seeder...');
        $exitCode = $this->call('db:seed', [
            '--class' => 'Database\\Seeders\\BlogSeeder',
            '--force' => true,
        ]);

        if ($exitCode !== self::SUCCESS) {
            $this->error('Blog post refresh failed.');

            return $exitCode;
        }

        $this->info('Done! Blog posts updated.');

        return self::SUCCESS;
    }
}
