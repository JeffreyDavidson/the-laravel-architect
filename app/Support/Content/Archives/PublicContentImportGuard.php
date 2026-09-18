<?php

declare(strict_types=1);

namespace App\Support\Content\Archives;

use Illuminate\Support\Uri;

final class PublicContentImportGuard
{
    public function allows(bool $staging): bool
    {
        $url = config('app.url');

        if (! is_string($url)) {
            return false;
        }

        $host = strtolower(rtrim(Uri::of($url)->host() ?? '', '.'));

        if ($host === '' || in_array($host, ['thelaravelarchitect.com', 'www.thelaravelarchitect.com'], true)) {
            return false;
        }

        return ! app()->isProduction() || ($staging && $host === 'staging.thelaravelarchitect.com');
    }
}
