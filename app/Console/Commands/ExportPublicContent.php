<?php

namespace App\Console\Commands;

use App\Content\PublicContentArchive;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Throwable;

#[Signature('content:export-public {path : Absolute path for the JSON archive}')]
#[Description('Export only currently public The Laravel Architect content')]
class ExportPublicContent extends Command
{
    public function handle(PublicContentArchive $archive): int
    {
        $path = $this->argument('path');

        if (! is_string($path) || ! str_starts_with($path, '/')) {
            $this->error('The public-content archive path must be absolute.');

            return self::FAILURE;
        }

        try {
            File::ensureDirectoryExists(dirname($path));
            $written = File::put($path, json_encode($archive->export(), JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        if ($written === false) {
            $this->error('Unable to write the public-content archive.');

            return self::FAILURE;
        }

        $this->info('Public-content archive written.');

        return self::SUCCESS;
    }
}
