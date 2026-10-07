<?php

declare(strict_types=1);

namespace App\Publishing;

use App\Enums\ReadinessCheck;
use App\Models\Episode;
use App\Models\NewsletterIssue;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use App\Models\Video;
use App\Support\Content\BundledPostArtwork;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

/**
 * The SQL form of the ContentReadiness checks, so lists and counts can filter on
 * readiness without loading records into memory. It modifies the builder it is
 * given. Every check matches one in ContentReadiness::checks() and gives the same
 * verdict for the same record; ContentReadinessCriteriaTest proves the two agree.
 *
 * @see ContentReadiness
 */
final readonly class ContentReadinessCriteria
{
    public function __construct(private BundledPostArtwork $bundledPostArtwork) {}

    /**
     * Keep the records that pass every readiness check for their content type.
     *
     * @param  Builder<covariant Model>  $query
     */
    public function whereReady(Builder $query): void
    {
        $this->whereComplete($query, $this->checks($query->getModel()));
    }

    /**
     * Keep the records that pass every one of the given checks.
     *
     * @param  Builder<covariant Model>  $query
     * @param  list<ReadinessCheck>  $checks
     */
    public function whereComplete(Builder $query, array $checks): void
    {
        foreach ($checks as $check) {
            $query->where($this->constraint($query->getModel(), $check));
        }
    }

    /**
     * Keep the records that fail at least one of the given checks.
     *
     * @param  Builder<covariant Model>  $query
     * @param  list<ReadinessCheck>  $checks
     */
    public function whereIncomplete(Builder $query, array $checks): void
    {
        $model = $query->getModel();

        $query->where(function (Builder $query) use ($model, $checks): void {
            foreach ($checks as $check) {
                $query->orWhereNot($this->constraint($model, $check));
            }
        });
    }

    /**
     * The checks for the model's content type, in ContentReadiness order.
     *
     * @return list<ReadinessCheck>
     */
    public function checks(Model $model): array
    {
        return array_map(ReadinessCheck::from(...), array_keys($this->constraints($model)));
    }

    private function constraint(Model $model, ReadinessCheck $check): Closure
    {
        return $this->constraints($model)[$check->value]
            ?? throw new InvalidArgumentException(sprintf('Unknown readiness check [%s] for [%s].', $check->value, $model::class));
    }

    /**
     * Each constraint keeps the records that pass the check. None of them can
     * evaluate to SQL NULL, so negating one keeps exactly the failing records.
     * Keyed by ReadinessCheck value.
     *
     * @return array<string, Closure>
     */
    private function constraints(Model $model): array
    {
        return match (true) {
            $model instanceof Post => [
                ReadinessCheck::Content->value => $this->filled('content'),
                ReadinessCheck::Excerpt->value => $this->filled('excerpt'),
                ReadinessCheck::FeaturedImage->value => fn (Builder $query): Builder => $query->whereRaw($this->filledSql('featured_image_path'))
                    ->orWhereIn('slug', $this->bundledPostArtwork->slugs()),
                ReadinessCheck::Category->value => fn (Builder $query): Builder => $query->whereNotNull('category_id'),
                ReadinessCheck::Tags->value => $this->hasTags(...),
                ReadinessCheck::SeoDescription->value => $this->hasSeoDescription('excerpt'),
            ],
            $model instanceof Project => [
                ReadinessCheck::Description->value => $this->filled('description'),
                ReadinessCheck::CaseStudy->value => $this->filled('content'),
                ReadinessCheck::FeaturedImage->value => $this->filled('featured_image_path'),
                ReadinessCheck::ProjectLink->value => fn (Builder $query): Builder => $query->whereRaw($this->filledSql('url'))
                    ->orWhereRaw($this->filledSql('github_url')),
                ReadinessCheck::TechStack->value => fn (Builder $query): Builder => $query->whereRaw($this->hasTechnologySql()),
                ReadinessCheck::Tags->value => $this->hasTags(...),
            ],
            $model instanceof Podcast => [
                ReadinessCheck::Description->value => $this->filled('description'),
                ReadinessCheck::LongDescription->value => $this->filled('long_description'),
                ReadinessCheck::CoverImage->value => $this->filled('cover_image_path'),
                ReadinessCheck::SubscribeLink->value => fn (Builder $query): Builder => $query->whereRaw($this->filledSql('apple_url'))
                    ->orWhereRaw($this->filledSql('spotify_url'))
                    ->orWhereRaw($this->filledSql('rss_url'))
                    ->orWhereRaw($this->filledSql('youtube_url')),
                ReadinessCheck::SeoDescription->value => $this->hasSeoDescription('description'),
            ],
            $model instanceof Episode => [
                ReadinessCheck::Podcast->value => fn (Builder $query): Builder => $query->whereNotNull('podcast_id'),
                ReadinessCheck::Description->value => $this->filled('description'),
                ReadinessCheck::EpisodeMedia->value => fn (Builder $query): Builder => $query->whereRaw($this->transistorShareUrlSql())
                    ->orWhereRaw($this->filledSql('youtube_url')),
                ReadinessCheck::ShowNotes->value => $this->filled('show_notes'),
                ReadinessCheck::FeaturedImage->value => $this->filled('featured_image_path'),
                ReadinessCheck::Tags->value => $this->hasTags(...),
                ReadinessCheck::SeoDescription->value => $this->hasSeoDescription('description'),
            ],
            $model instanceof NewsletterIssue => [
                ReadinessCheck::Content->value => $this->filled('content'),
                ReadinessCheck::Excerpt->value => $this->filled('excerpt'),
                ReadinessCheck::SeoDescription->value => $this->hasSeoDescription('excerpt'),
            ],
            $model instanceof Video => [
                ReadinessCheck::Title->value => $this->filled('title'),
                ReadinessCheck::YoutubeId->value => $this->filled('youtube_id'),
                ReadinessCheck::Description->value => $this->filled('description'),
                ReadinessCheck::Thumbnail->value => $this->filled('thumbnail_url'),
                ReadinessCheck::Duration->value => $this->filled('duration'),
                ReadinessCheck::Synced->value => fn (Builder $query): Builder => $query->whereNotNull('synced_at'),
            ],
            default => throw new InvalidArgumentException(sprintf('[%s] has no readiness checks.', $model::class)),
        };
    }

    /** @param  literal-string  $column */
    private function filled(string $column): Closure
    {
        return fn (Builder $query): Builder => $query->whereRaw($this->filledSql($column));
    }

    /**
     * @param  Builder<Model>  $query
     * @return Builder<Model>
     */
    private function hasTags(Builder $query): Builder
    {
        return $query->whereHas('tags');
    }

    /**
     * An SEO description set on the record's SEO row, or the model column that
     * getDynamicSEOData() falls back to.
     *
     * @param  literal-string  $fallbackColumn
     */
    private function hasSeoDescription(string $fallbackColumn): Closure
    {
        return fn (Builder $query): Builder => $query->whereHas('seo', fn (Builder $seo): Builder => $seo->whereRaw($this->filledSql('seo.description')))
            ->orWhereRaw($this->filledSql($fallbackColumn));
    }

    /**
     * SQL for PHP's filled(): not null and not only whitespace, trimming the same
     * characters as PHP's trim().
     *
     * @param  literal-string  $column
     * @return literal-string
     */
    private function filledSql(string $column): string
    {
        return "trim(coalesce({$column}, ''), char(32, 9, 10, 11, 13, 0)) <> ''";
    }

    /**
     * SQL for Project::technologies() !== []: at least one string entry that is
     * not only whitespace.
     *
     * @return literal-string
     */
    private function hasTechnologySql(): string
    {
        return "exists (select 1 from json_each(coalesce(tech_stack, '[]')) where type = 'text' and {$this->filledSql('value')})";
    }

    /**
     * SQL for Episode::transistorEmbedUrl() !== null: the 30-character prefix
     * https://share.transistor.fm/s/ followed by a letters-and-digits ID and at
     * most one trailing slash. GLOB is case-sensitive, like the PHP pattern.
     *
     * @return literal-string
     */
    private function transistorShareUrlSql(): string
    {
        return "(coalesce(transistor_url, '') glob 'https://share.transistor.fm/s/?*' and ("
            ."substr(transistor_url, 31) not glob '*[^a-zA-Z0-9]*'"
            ." or (substr(transistor_url, 31) glob '?*/' and substr(transistor_url, 31, length(transistor_url) - 31) not glob '*[^a-zA-Z0-9]*')"
            .'))';
    }
}
