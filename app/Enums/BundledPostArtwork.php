<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * The artwork bundled with the application for the launch posts, backed by the post slug. A post
 * that has no uploaded featured image falls back to it, so a fresh or seeded copy of the site still
 * has artwork. PostPresenter turns the image name into Vite asset URLs.
 */
enum BundledPostArtwork: string
{
    case HelloWorld = 'hello-world-why-im-starting-this-blog';
    case KansasToFlorida = 'from-kansas-to-florida-a-developers-journey';
    case ProjectStructure = 'how-i-structure-every-laravel-project';
    case WhyLaravel = 'why-i-still-choose-laravel-in-2026';
    case FifteenYears = 'what-15-years-of-web-development-taught-me';

    /** The image name under resources/images, which ships in 384, 768 and 1280 pixel WebP sizes. */
    public function imageName(): string
    {
        return match ($this) {
            self::HelloWorld => 'post-hello-world',
            self::KansasToFlorida => 'post-kansas-florida',
            self::ProjectStructure => 'home-writing-fallback',
            self::WhyLaravel => 'home-writing-review',
            self::FifteenYears => 'home-writing-modules',
        };
    }

    /** @return list<string> The slugs of the posts that have bundled artwork. */
    public static function slugs(): array
    {
        return array_map(
            fn (self $artwork): string => $artwork->value,
            self::cases(),
        );
    }
}
