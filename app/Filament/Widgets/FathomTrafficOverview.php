<?php

namespace App\Filament\Widgets;

use App\Services\FathomAnalyticsService;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class FathomTrafficOverview extends StatsOverviewWidget
{
    #[\Override]
    protected ?string $pollingInterval = null;

    #[\Override]
    protected static ?int $sort = 3;

    #[\Override]
    protected ?string $heading = 'Fathom traffic';

    #[\Override]
    protected ?string $description = 'Aggregate traffic and conversion activity from the last 30 days.';

    /** @return list<Stat> */
    protected function getStats(): array
    {
        $overview = FathomAnalyticsService::overview();

        if ($overview === null) {
            $configured = FathomAnalyticsService::isConfigured();

            return [
                Stat::make('Fathom Analytics', $configured ? 'Unavailable' : 'Not configured')
                    ->description($configured ? 'Check the API token and try again.' : 'Set FATHOM_API_TOKEN to show traffic.')
                    ->descriptionIcon(Heroicon::OutlinedChartBar)
                    ->color('gray'),
            ];
        }

        return [
            Stat::make('Page views', Number::format($overview['pageviews']))
                ->description('Last 30 days')
                ->descriptionIcon(Heroicon::OutlinedEye)
                ->color('info'),
            Stat::make('Visits', Number::format($overview['visits']))
                ->description('Last 30 days')
                ->descriptionIcon(Heroicon::OutlinedChartBar)
                ->color('primary'),
            Stat::make('Bounce rate', "{$overview['bounce_rate']}%")
                ->description('Last 30 days')
                ->descriptionIcon(Heroicon::OutlinedArrowTrendingDown)
                ->color('warning'),
            Stat::make('Newsletter signups', Number::format($overview['newsletter_signups']))
                ->description('Tracked conversions')
                ->descriptionIcon(Heroicon::OutlinedEnvelope)
                ->color('success'),
            Stat::make('Contact submissions', Number::format($overview['contact_submissions']))
                ->description('Tracked conversions')
                ->descriptionIcon(Heroicon::OutlinedChatBubbleLeftRight)
                ->color('success'),
            Stat::make(
                'Outbound clicks',
                Number::format($overview['project_live_link_clicks'] + $overview['github_profile_clicks']),
            )
                ->description('Projects and GitHub')
                ->descriptionIcon(Heroicon::OutlinedArrowTopRightOnSquare)
                ->color('gray'),
        ];
    }
}
