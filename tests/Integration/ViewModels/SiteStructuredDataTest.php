<?php

use App\Models\Episode;
use App\Models\NewsletterIssue;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use App\Models\Subscriber;
use App\ViewModels\EpisodeShowViewModel;
use App\ViewModels\NewsletterConfirmationViewModel;
use App\ViewModels\NewsletterConfirmedViewModel;
use App\ViewModels\NewsletterIndexViewModel;
use App\ViewModels\NewsletterIssueViewModel;
use App\ViewModels\NewsletterUnsubscriptionViewModel;
use App\ViewModels\PostShowViewModel;
use App\ViewModels\ProjectShowViewModel;
use App\ViewModels\SearchViewModel;
use App\ViewModels\ServiceViewModel;
use App\ViewModels\SiteStructuredData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JeffreyDavidson\CreatorKit\Data\PageMeta;
use RalphJSmit\Laravel\SEO\Support\SEOData;
use Tests\Support\StructuredDataExpectations as Schema;

pest()->use(RefreshDatabase::class);

it('starts every graph with the website and its author, then the page nodes', function () {
    Schema::useFixedOrigin();
    $pageNode = ['@type' => 'Thing', 'name' => 'A page node'];

    $graph = app(SiteStructuredData::class)
        ->graph(new PageMeta(new SEOData, [$pageNode]));

    expect($graph)->toBe([Schema::website(), $pageNode]);
});

it('describes only the website on pages without their own schema', function (PageMeta $pageMeta) {
    Schema::useFixedOrigin();

    $graph = Schema::graph($pageMeta);

    expect($graph)->toBe([Schema::website()]);
})->with([
    'newsletter index' => fn (): PageMeta => app(NewsletterIndexViewModel::class)->data()['pageMeta'],
    'newsletter issue' => function (): PageMeta {
        $issue = NewsletterIssue::factory()
            ->published()
            ->create();

        return app(NewsletterIssueViewModel::class)->data($issue)['pageMeta'];
    },
    'search' => fn (): PageMeta => app(SearchViewModel::class)->data('laravel')['pageMeta'],
    'services' => fn (): PageMeta => app(ServiceViewModel::class)->data()['pageMeta'],
    'newsletter confirmation' => function (): PageMeta {
        $subscriber = Subscriber::factory()
            ->pending()
            ->create();

        return app(NewsletterConfirmationViewModel::class)->data($subscriber, 'https://example.test/confirm')['pageMeta'];
    },
    'newsletter unsubscription' => fn (): PageMeta => app(NewsletterUnsubscriptionViewModel::class)->data(Subscriber::factory()->create(), 'https://example.test/unsubscribe')['pageMeta'],
    'newsletter confirmed' => fn (): PageMeta => app(NewsletterConfirmedViewModel::class)->data()['pageMeta'],
    'post preview' => fn (): PageMeta => app(PostShowViewModel::class)->previewData(Post::factory()->create())['pageMeta'],
    'project preview' => fn (): PageMeta => app(ProjectShowViewModel::class)->previewData(Project::factory()->create())['pageMeta'],
    'episode preview' => function (): PageMeta {
        $podcast = Podcast::factory()->create();
        $episode = Episode::factory()
            ->for($podcast)
            ->create();

        return app(EpisodeShowViewModel::class)->previewData($podcast, $episode)['pageMeta'];
    },
    'newsletter issue preview' => fn (): PageMeta => app(NewsletterIssueViewModel::class)->previewData(NewsletterIssue::factory()->create())['pageMeta'],
]);
