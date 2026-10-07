<?php

declare(strict_types=1);

namespace App\Support\Content;

use Illuminate\Support\Facades\Vite;

/**
 * The artwork bundled with the application for the launch posts. A post that has no uploaded
 * featured image falls back to it, so a fresh or seeded copy of the site still has artwork.
 */
final class BundledPostArtwork
{
    /** @var array<string, string> Post slug => image name under resources/images. */
    private const array IMAGES = [
        'hello-world-why-im-starting-this-blog' => 'post-hello-world',
        'from-kansas-to-florida-a-developers-journey' => 'post-kansas-florida',
        'how-i-structure-every-laravel-project' => 'home-writing-fallback',
        'why-i-still-choose-laravel-in-2026' => 'home-writing-review',
        'what-15-years-of-web-development-taught-me' => 'home-writing-modules',
    ];

    public function exists(string $slug): bool
    {
        return array_key_exists($slug, self::IMAGES);
    }

    /** @return list<string> The slugs of the posts that have bundled artwork. */
    public function slugs(): array
    {
        return array_keys(self::IMAGES);
    }

    /** @return array{small: string, medium: string, large: string}|null */
    public function urls(string $slug): ?array
    {
        $image = self::IMAGES[$slug] ?? null;

        if ($image === null) {
            return null;
        }

        return [
            'small' => Vite::asset("resources/images/{$image}-384.webp"),
            'medium' => Vite::asset("resources/images/{$image}-768.webp"),
            'large' => Vite::asset("resources/images/{$image}-1280.webp"),
        ];
    }
}
