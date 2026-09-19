<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\YouTubeService;
use App\Services\YouTubeVideoStatsSynchronizer;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('youtube:stats')]
#[Description('Update view/like/comment counts for all synced videos')]
class YouTubeStats extends Command
{
    public function handle(YouTubeService $youtube, YouTubeVideoStatsSynchronizer $synchronizer): int
    {
        try {
            ['videoCount' => $videoCount, 'updated' => $updated] = $synchronizer->synchronize($youtube);
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        if ($videoCount === 0) {
            $this->info('No videos to update. Run youtube:sync first.');

            return self::SUCCESS;
        }

        $this->info("Updating stats for {$videoCount} videos...");

        $this->info("Updated stats for {$updated} videos.");

        return self::SUCCESS;
    }
}
