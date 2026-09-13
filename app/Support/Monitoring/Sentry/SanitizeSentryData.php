<?php

namespace App\Support\Monitoring\Sentry;

use Sentry\Breadcrumb;

final class SanitizeSentryData
{
    /**
     * @param  array<array-key, mixed>  $data
     * @return array<string, mixed>
     */
    public function array(array $data): array
    {
        $sanitized = [];

        foreach ($data as $key => $value) {
            $key = (string) $key;
            $normalizedKey = strtolower((string) preg_replace('/[^a-z0-9]+/i', '_', $key));

            if ($this->isSensitiveKey($normalizedKey)) {
                $sanitized[$key] = '[Filtered]';

                continue;
            }

            if (in_array($normalizedKey, ['url', 'uri'], true) && is_string($value)) {
                $sanitized[$key] = $this->url($value);

                continue;
            }

            if ($normalizedKey === 'path' && is_string($value)) {
                $sanitized[$key] = $this->path($value);

                continue;
            }

            if ($normalizedKey === 'query_string') {
                $sanitized[$key] = '[Filtered]';

                continue;
            }

            $sanitized[$key] = is_array($value)
                ? $this->array($value)
                : (is_string($value) ? $this->string($value) : $value);
        }

        return $sanitized;
    }

    public function url(string $url): string
    {
        $parts = parse_url($url);

        if (! is_array($parts)) {
            return '[Filtered]';
        }

        $path = $this->path((string) ($parts['path'] ?? ''));
        $scheme = $parts['scheme'] ?? null;
        $host = $parts['host'] ?? null;

        if (! is_string($scheme) || ! is_string($host)) {
            return '[Filtered]';
        }

        $host = str_contains($host, ':') ? "[{$host}]" : $host;
        $port = isset($parts['port']) && is_int($parts['port']) ? ":{$parts['port']}" : '';

        return strtolower($scheme)."://{$host}{$port}".($path !== '' ? $path : '/');
    }

    public function path(string $path): string
    {
        if ($path === '') {
            return '';
        }

        $segments = explode('/', $path);
        $previousSegment = null;

        foreach ($segments as $index => $segment) {
            if ($previousSegment !== null && in_array(strtolower($previousSegment), ['confirm', 'confirmation'], true)) {
                $segments[$index] = '[Filtered]';
            } elseif (preg_match('/^[a-z0-9_-]{32,}$/i', $segment) === 1) {
                $segments[$index] = '[Filtered]';
            }

            $previousSegment = $segment;
        }

        return implode('/', $segments);
    }

    public function string(string $value): string
    {
        $value = preg_replace_callback(
            '~https?://[^\s<>"\']+~i',
            fn (array $match): string => $this->url($match[0]),
            $value,
        ) ?? '[Filtered]';

        return preg_replace('/\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}\b/i', '[Filtered]', $value) ?? '[Filtered]';
    }

    public function breadcrumb(Breadcrumb $breadcrumb): Breadcrumb
    {
        $sanitized = $breadcrumb;
        $message = $breadcrumb->getMessage();

        if ($message !== null) {
            $sanitized = $sanitized->withMessage($this->string($message));
        }

        foreach ($breadcrumb->getMetadata() as $key => $value) {
            $sanitized = $sanitized->withMetadata($key, $this->array([$key => $value])[$key]);
        }

        return $sanitized;
    }

    private function isSensitiveKey(string $key): bool
    {
        return preg_match('/(?:password|secret|token|api[_-]?key|authorization|cookie|set_cookie|email|body|payload|input|data)/', $key) === 1;
    }
}
