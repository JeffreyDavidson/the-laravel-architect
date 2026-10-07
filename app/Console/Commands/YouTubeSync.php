<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\YouTubeVideoSyncWorkflow;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('youtube:sync {--limit=50 : Maximum videos to fetch}')]
#[Description('Sync videos from YouTube channel')]
final class YouTubeSync extends Command
{
    public function handle(YouTubeVideoSyncWorkflow $workflow): int
    {
        $this->info('Fetching videos from YouTube...');

        try {
            ['created' => $created, 'updated' => $updated] = $workflow->synchronize((int) $this->option('limit'));
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info("Done! {$created} new, {$updated} updated.");

        return self::SUCCESS;
    }
}
