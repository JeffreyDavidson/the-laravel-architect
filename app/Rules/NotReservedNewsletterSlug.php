<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as Router;
use Illuminate\Support\Str;
use Illuminate\Translation\PotentiallyTranslatedString;

/**
 * Rejects a newsletter issue slug taken by a static /newsletter/* route. Those routes are
 * registered before the issue route, so an issue with the same slug would be unreachable.
 */
final readonly class NotReservedNewsletterSlug implements ValidationRule
{
    /**
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (is_string($value) && in_array($value, $this->reservedSlugs(), true)) {
            $fail('This slug is already used by another newsletter page. Choose a different slug.');
        }
    }

    /** @return array<int, string> */
    private function reservedSlugs(): array
    {
        return collect(Router::getRoutes()->getRoutes())
            ->map(fn (Route $route): string => $route->uri())
            ->filter(fn (string $uri): bool => preg_match('#\Anewsletter/[^/{]+\z#', $uri) === 1)
            ->map(fn (string $uri): string => Str::after($uri, 'newsletter/'))
            ->unique()
            ->values()
            ->all();
    }
}
