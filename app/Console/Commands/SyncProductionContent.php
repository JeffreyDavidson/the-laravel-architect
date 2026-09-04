<?php

namespace App\Console\Commands;

use App\Content\ProductionContentSource;
use App\Content\PublicContentArchive;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Throwable;

#[Signature('content:sync-production')]
#[Description('Replace staging public content and media with the current production versions')]
class SyncProductionContent extends Command
{
    public function handle(PublicContentArchive $archive, ProductionContentSource $source): int
    {
        if (app()->isProduction()) {
            $this->error('Production content sync may only run in a non-production environment.');

            return self::FAILURE;
        }

        $directory = storage_path('framework/content-sync/'.Str::uuid());
        $archivePath = "{$directory}/content.json";
        File::ensureDirectoryExists($directory);

        try {
            $source->exportTo($archivePath);
            $contents = $archive->decode(File::get($archivePath));

            $mediaPaths = $archive->mediaPaths($contents);
            $source->copyMedia($mediaPaths);
            $counts = $archive->sync($contents);

            $this->info(collect($counts)->map(fn (int $count, string $type): string => "{$count} {$type}")->join(', ').' synchronized.');
            $this->info(count($mediaPaths).' referenced public media files synchronized.');

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        } finally {
            File::deleteDirectory($directory);
        }
    }
}
