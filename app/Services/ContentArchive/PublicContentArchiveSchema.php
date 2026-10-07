<?php

declare(strict_types=1);

namespace App\Services\ContentArchive;

/**
 * The public content archive format shared by the exporter, importer and relations.
 */
final class PublicContentArchiveSchema
{
    public const int VERSION = 1;

    public const array POST_FIELDS = ['title', 'slug', 'excerpt', 'content', 'featured_image_path', 'published_at'];

    public const array PROJECT_FIELDS = ['title', 'slug', 'description', 'content', 'featured_image_path', 'url', 'github_url', 'tech_stack', 'is_featured', 'sort_order'];

    public const array PODCAST_FIELDS = ['name', 'slug', 'description', 'long_description', 'cover_image_path', 'color', 'apple_url', 'spotify_url', 'rss_url', 'youtube_url', 'sort_order'];

    public const array EPISODE_FIELDS = ['title', 'slug', 'episode_number', 'season_number', 'description', 'show_notes', 'transcript', 'featured_image_path', 'youtube_url', 'duration_seconds', 'guest_name', 'guest_title', 'guest_url', 'transistor_url', 'published_at'];

    public const array NEWSLETTER_ISSUE_FIELDS = ['title', 'slug', 'excerpt', 'content', 'published_at'];

    public const array VIDEO_FIELDS = ['youtube_id', 'title', 'slug', 'description', 'thumbnail_url', 'duration', 'view_count', 'like_count', 'comment_count', 'is_featured', 'published_at', 'synced_at'];

    public const array SEO_FIELDS = ['description', 'title', 'image', 'author', 'robots', 'canonical_url'];

    /**
     * Select the given fields that are present in the attributes, in field order.
     *
     * @param  array<string, mixed>  $attributes
     * @param  list<string>  $fields
     * @return array<string, mixed>
     */
    public static function only(array $attributes, array $fields): array
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
