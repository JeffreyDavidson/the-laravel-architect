<?php

declare(strict_types=1);

namespace App\Services\ContentArchive;

use InvalidArgumentException;

class PublicContentArchiveValidator
{
    /**
     * @param  array<string, mixed>  $archive
     * @return array{categories: list<array<string, mixed>>, posts: list<array<string, mixed>>, projects: list<array<string, mixed>>, podcasts: list<array<string, mixed>>, episodes: list<array<string, mixed>>, newsletter_issues: list<array<string, mixed>>, videos: list<array<string, mixed>>}
     */
    public function validate(array $archive, int $version): array
    {
        if (($archive['version'] ?? null) !== $version) {
            throw new InvalidArgumentException('The public content archive version is not supported.');
        }

        $records = [];

        foreach (['categories', 'posts', 'projects', 'podcasts', 'episodes', 'videos'] as $type) {
            if (! isset($archive[$type]) || ! is_array($archive[$type])) {
                throw new InvalidArgumentException("The public content archive is missing {$type}.");
            }

            $records[$type] = [];

            foreach ($archive[$type] as $attributes) {
                if (! is_array($attributes)) {
                    throw new InvalidArgumentException("The public content archive contains invalid {$type}.");
                }

                $record = [];

                foreach ($attributes as $key => $value) {
                    if (! is_string($key)) {
                        throw new InvalidArgumentException("The public content archive contains invalid {$type}.");
                    }

                    $record[$key] = $value;
                }

                $records[$type][] = $record;
            }
        }

        $newsletterIssues = $archive['newsletter_issues'] ?? [];

        if (! is_array($newsletterIssues)) {
            throw new InvalidArgumentException('The public content archive contains invalid newsletter issues.');
        }

        $records['newsletter_issues'] = [];

        foreach ($newsletterIssues as $attributes) {
            if (! is_array($attributes)) {
                throw new InvalidArgumentException('The public content archive contains invalid newsletter issues.');
            }

            $record = [];

            foreach ($attributes as $key => $value) {
                if (! is_string($key)) {
                    throw new InvalidArgumentException('The public content archive contains invalid newsletter issues.');
                }

                $record[$key] = $value;
            }

            $records['newsletter_issues'][] = $record;
        }

        return $records;
    }

    public function validateMediaPath(mixed $path): string
    {
        if (! is_string($path)
            || str_starts_with($path, '/')
            || str_contains($path, '\\')
            || preg_match('/[\x00-\x1F\x7F]/', $path) === 1
            || in_array('..', explode('/', $path), true)) {
            throw new InvalidArgumentException('The public content archive contains an unsafe media path.');
        }

        return $path;
    }
}
