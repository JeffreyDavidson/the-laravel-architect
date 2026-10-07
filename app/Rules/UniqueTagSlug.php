<?php

declare(strict_types=1);

namespace App\Rules;

use App\Models\Tag;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

/**
 * Rejects a tag slug already used by another tag, of any type, in the current locale.
 * Tag slugs are translatable JSON, which the standard unique rule cannot read.
 */
final readonly class UniqueTagSlug implements ValidationRule
{
    public function __construct(private ?Tag $ignore = null) {}

    /**
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            return;
        }

        $locale = app()->getLocale();
        $query = Tag::query()->where("slug->{$locale}", $value);

        if ($this->ignore instanceof Tag) {
            $query->whereKeyNot($this->ignore->getKey());
        }

        if ($query->exists()) {
            $fail('The slug has already been taken.');
        }
    }
}
