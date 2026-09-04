<?php

namespace App\Console\Commands;

use App\Content\PublicContentArchive;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Uri;
use Throwable;

#[Signature('content:import-public {path : Absolute path to a JSON archive} {--staging : Permit an import on the staging hostname when APP_ENV is production}')]
#[Description('Import a public-content archive into staging or another non-production environment')]
class ImportPublicContent extends Command
{
    public function handle(PublicContentArchive $archive): int
    {
        if ($this->importIsBlocked()) {
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

    private function importIsBlocked(): bool
    {
        $url = config('app.url');

        if (! is_string($url)) {
            return true;
        }

        $host = Uri::of($url)->host();

        if (in_array($host, ['thelaravelarchitect.com', 'www.thelaravelarchitect.com'], true)) {
            return true;
        }

        if (! app()->isProduction()) {
            return false;
        }

        return ! $this->option('staging') || $host !== 'staging.thelaravelarchitect.com';
    }
}
