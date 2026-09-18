<?php

namespace App\Console\Commands;

use App\Support\Content\Archives\PublicContentArchive;
use App\Support\Content\Archives\PublicContentImportGuard;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Throwable;

#[Signature('content:import-public {path : Absolute path to a JSON archive} {--staging : Permit an import on the staging hostname when APP_ENV is production}')]
#[Description('Import a public-content archive into staging or another non-production environment')]
class ImportPublicContent extends Command
{
    public function handle(PublicContentArchive $archive, PublicContentImportGuard $guard): int
    {
        if (! $guard->allows((bool) $this->option('staging'))) {
            $this->error('Public content cannot be imported into production.');

            return self::FAILURE;
        }

        $path = $this->argument('path');

        if (! is_string($path) || ! File::isFile($path)) {
            $this->error('The public-content archive was not found.');

            return self::FAILURE;
        }

        try {
            $counts = $archive->sync($archive->decode(File::get($path)));
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info(collect($counts)->map(fn (int $count, string $type): string => "{$count} {$type}")->join(', ').' synchronized.');

        return self::SUCCESS;
    }
}
