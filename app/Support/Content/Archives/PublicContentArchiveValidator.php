<?php

namespace App\Support\Content\Archives;

use InvalidArgumentException;

class PublicContentArchiveValidator
{
    /**
     * @param  array<string, mixed>  $archive
     * @return array{categories: list<array<string, mixed>>, posts: list<array<string, mixed>>, projects: list<array<string, mixed>>, podcasts: list<array<string, mixed>>, episodes: list<array<string, mixed>>, videos: list<array<string, mixed>>}
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

        return $records;
    }
}
