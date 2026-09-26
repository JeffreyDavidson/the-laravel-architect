<?php

namespace App\Filament\Widgets;

use App\Enums\ContactInquiryStatus;
use App\Enums\PublishStatus;
use App\Filament\Resources\ContactInquiries\ContactInquiryResource;
use App\Filament\Resources\Episodes\EpisodeResource;
use App\Filament\Resources\NewsletterIssues\NewsletterIssueResource;
use App\Filament\Resources\Posts\PostResource;
use App\Filament\Resources\Subscribers\SubscriberResource;
use App\Models\ContactInquiry;
use App\Models\Episode;
use App\Models\NewsletterIssue;
use App\Models\Post;
use App\Models\Subscriber;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class EditorialOperationsOverview extends StatsOverviewWidget
{
    #[\Override]
    protected ?string $pollingInterval = null;

    #[\Override]
    protected static ?int $sort = 3;

    #[\Override]
    protected ?string $heading = 'Editorial operations';

    #[\Override]
    protected ?string $description = 'Work that needs a decision or follow-up.';

    /** @return list<Stat> */
    protected function getStats(): array
    {
        return [
            Stat::make('New inquiries', ContactInquiry::query()->where('status', ContactInquiryStatus::New)
                ->count())
                ->description('Private inbox')
                ->descriptionIcon(Heroicon::OutlinedChatBubbleLeftRight)
                ->color('info')
                ->url(ContactInquiryResource::getUrl('index')),
            Stat::make('Posts in review', Post::query()->where('status', PublishStatus::InReview)
                ->count())
                ->description('Awaiting approval')
                ->descriptionIcon(Heroicon::OutlinedEye)
                ->color('warning')
                ->url(PostResource::getUrl('index')),
            Stat::make('Scheduled posts', Post::query()->scheduled()
                ->count())
                ->description('Ready to publish')
                ->descriptionIcon(Heroicon::OutlinedCalendar)
                ->color('success')
                ->url(PostResource::getUrl('index', ['filters' => ['publication' => ['value' => PublishStatus::Scheduled->value]]])),
            Stat::make('Episode queue', Episode::query()->unpublished()
                ->count())
                ->description('Unpublished episodes')
                ->descriptionIcon(Heroicon::OutlinedMusicalNote)
                ->color('primary')
                ->url(EpisodeResource::getUrl('index', ['filters' => ['unpublished' => ['isActive' => true]]])),
            Stat::make('Newsletter queue', NewsletterIssue::query()->unpublished()
                ->count())
                ->description('Unpublished issues')
                ->descriptionIcon(Heroicon::OutlinedNewspaper)
                ->color('gray')
                ->url(NewsletterIssueResource::getUrl('index', ['filters' => ['unpublished' => ['isActive' => true]]])),
            Stat::make('Active subscribers', Subscriber::query()->active()
                ->count())
                ->description('Confirmed audience')
                ->descriptionIcon(Heroicon::OutlinedEnvelope)
                ->color('success')
                ->url(SubscriberResource::getUrl('index')),
        ];
    }
}
