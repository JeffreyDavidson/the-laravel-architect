<?php

namespace App\Content;

use InvalidArgumentException;

class PublicContentArchiveMedia
{
    public function validatePath(mixed $path): string
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
