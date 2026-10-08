<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * One item of the content readiness checklist. ContentReadiness decides whether a
 * record passes it, and ContentReadinessCriteria applies the same check in SQL.
 */
enum ReadinessCheck: string implements HasLabel
{
    case Title = 'title';
    case Content = 'content';
    case Excerpt = 'excerpt';
    case Description = 'description';
    case LongDescription = 'long_description';
    case CaseStudy = 'case_study';
    case FeaturedImage = 'featured_image';
    case CoverImage = 'cover_image';
    case Category = 'category';
    case Tags = 'tags';
    case SeoDescription = 'seo_description';
    case ProjectLink = 'project_link';
    case TechStack = 'tech_stack';
    case SubscribeLink = 'subscribe_link';
    case Podcast = 'podcast';
    case EpisodeMedia = 'episode_media';
    case ShowNotes = 'show_notes';
    case YoutubeId = 'youtube_id';
    case Thumbnail = 'thumbnail';
    case Duration = 'duration';
    case Synced = 'synced';

    public function getLabel(): string
    {
        return match ($this) {
            self::Title => 'Title',
            self::Content => 'Content',
            self::Excerpt => 'Excerpt',
            self::Description => 'Description',
            self::LongDescription => 'About section',
            self::CaseStudy => 'Case study',
            self::FeaturedImage => 'Featured image',
            self::CoverImage => 'Cover image',
            self::Category => 'Category',
            self::Tags => 'Tags',
            self::SeoDescription => 'SEO description',
            self::ProjectLink => 'Project link',
            self::TechStack => 'Tech stack',
            self::SubscribeLink => 'Subscribe link',
            self::Podcast => 'Podcast',
            self::EpisodeMedia => 'Episode media',
            self::ShowNotes => 'Show notes',
            self::YoutubeId => 'YouTube video',
            self::Thumbnail => 'Thumbnail',
            self::Duration => 'Duration',
            self::Synced => 'YouTube sync',
        };
    }
}
