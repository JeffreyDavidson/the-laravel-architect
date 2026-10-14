<?php

/**
 * @return array<array-key, mixed>
 */
function decodeStructuredData(string|false $content): array
{
    if (! is_string($content)) {
        throw new RuntimeException('The response did not contain HTML content.');
    }

    if (preg_match('/<script[^>]*type="application\/ld\+json"[^>]*>(.*?)<\/script>/s', $content, $matches) !== 1) {
        throw new RuntimeException('The response did not contain structured data.');
    }

    $json = $matches[1];

    $structuredData = json_decode($json, true, flags: JSON_THROW_ON_ERROR);

    if (! is_array($structuredData)) {
        throw new RuntimeException('The structured data was not an object.');
    }

    return $structuredData;
}

/**
 * @return list<array<array-key, mixed>>
 */
function structuredDataGraph(mixed $value): array
{
    if (! is_array($value)) {
        throw new RuntimeException('The structured data graph was not an array.');
    }

    $graph = [];

    foreach ($value as $item) {
        if (! is_array($item)) {
            throw new RuntimeException('The structured data graph contained an invalid item.');
        }

        $graph[] = $item;
    }

    return $graph;
}

/**
 * @return array<array-key, mixed>
 */
function structuredDataObject(mixed $value): array
{
    if (! is_array($value)) {
        throw new RuntimeException('The structured data item was not an object.');
    }

    return $value;
}

/**
 * @return array<array-key, mixed>
 */
function structuredDataListItem(mixed $value, int $index): array
{
    if (! is_array($value)) {
        throw new RuntimeException('The structured data list was not an array.');
    }

    return structuredDataObject($value[$index] ?? null);
}

/**
 * @return array<string, array{file: string}>
 */
function assetManifest(): array
{
    $content = file_get_contents(public_path('build/manifest.json'));

    if (! is_string($content)) {
        throw new RuntimeException('The Vite manifest could not be read.');
    }

    $decoded = json_decode($content, true, flags: JSON_THROW_ON_ERROR);

    if (! is_array($decoded)) {
        throw new RuntimeException('The Vite manifest was not an object.');
    }

    $manifest = [];

    foreach ($decoded as $key => $entry) {
        if (! is_string($key) || ! is_array($entry) || ! is_string($entry['file'] ?? null)) {
            throw new RuntimeException('The Vite manifest contained an invalid entry.');
        }

        $manifest[$key] = ['file' => $entry['file']];
    }

    return $manifest;
}

function responseContent(string|false $content): string
{
    if (! is_string($content)) {
        throw new RuntimeException('The response did not contain HTML content.');
    }

    return $content;
}

function stringPosition(string $haystack, string $needle): int
{
    $position = strpos($haystack, $needle);

    if ($position === false) {
        throw new RuntimeException("The response did not contain {$needle}.");
    }

    return $position;
}

function configuredString(mixed $value): string
{
    if (! is_string($value) && ! is_int($value) && ! is_float($value)) {
        throw new RuntimeException('The configured value was not a string.');
    }

    return (string) $value;
}
