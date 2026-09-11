<?php

namespace App\Support\Content\Archives;

use App\Models\Episode;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Spatie\Tags\Tag;

class PublicContentArchiveRelations
{
    /** @param list<array{name: string, type?: string|null}> $tags */
    public function syncTags(Post|Project|Episode $model, array $tags): void
    {
        $model->syncTags(collect($tags)->map(
            fn (array $tag): Tag => $this->findOrCreateTag($tag['name'], $tag['type'] ?? null),
        )->all());
    }

    /**
     * @param  array<string, mixed>|null  $attributes
     * @param  list<string>  $fields
     */
    public function syncSeo(Post|Project|Podcast|Episode $model, ?array $attributes, array $fields): void
    {
        if ($attributes === null) {
            $model->seo()->delete();

            return;
        }

        $model->seo()->updateOrCreate([], $this->only($attributes, $fields));
    }

    /** @return list<array{name: string, type?: string|null}> */
    public function tagRecords(mixed $value): array
    {
        if (! is_array($value)) {
            throw new InvalidArgumentException('The public content archive contains invalid tags.');
        }

        $tags = [];

        foreach ($value as $tag) {
            if (! is_array($tag)) {
                throw new InvalidArgumentException('The public content archive contains invalid tags.');
            }

            $name = $tag['name'] ?? null;
            $type = $tag['type'] ?? null;

            if (! is_string($name) || $name === '' || ($type !== null && ! is_string($type))) {
                throw new InvalidArgumentException('The public content archive contains invalid tags.');
            }

            $tags[] = ['name' => $name, 'type' => $type];
        }

        return $tags;
    }

    private function findOrCreateTag(string $name, ?string $type): Tag
    {
        $locale = app()->getLocale();
        $tag = Tag::findFromString($name, $type, $locale);

        if ($tag instanceof Tag) {
            return $tag;
        }

        $tag = new Tag;
        $tag->setTranslation('name', $locale, $name);
        $tag->setTranslation('slug', $locale, Str::slug($name));
        $tag->type = $type;
        $tag->save();

        return $tag;
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @param  list<string>  $fields
     * @return array<string, mixed>
     */
    private function only(array $attributes, array $fields): array
    {
        $selected = [];

        foreach ($fields as $field) {
            if (array_key_exists($field, $attributes)) {
                $selected[$field] = $attributes[$field];
            }
        }

        return $selected;
    }
}
