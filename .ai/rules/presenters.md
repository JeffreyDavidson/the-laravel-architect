---
paths:
  - 'app/Presenters/**'
---

# Presenters

## Keep presenters focused on model presentation
Presenters wrap one model and expose reusable display formatting only. They must not fetch records, aggregate data, or perform application operations. `tests/Architecture/PresenterArchitectureTest.php` forbids `App\Queries` and the `DB` facade.

## Let presenters own URLs and media display
Models expose stored paths and IDs; presenters build every URL from them: stored-image URLs (`featuredImageUrl()`, `coverImageUrl()`), responsive images and srcsets, bundled artwork (`BundledPostArtwork` enum plus Vite), YouTube and Transistor links, signed newsletter links (`SubscriberPresenter`) and the public and signed preview pages of publishable content (`publicUrl()`, `previewUrl()`, `publicOrPreviewUrl()` through `Concerns\LinksToPublicPageOrPreview`, which defines the two-hour preview lifetime once). Queries return records and the caller turns them into links through a presenter.

## Describe one model's schema.org node on its presenter
A model's own JSON-LD node is display formatting of that model, so it lives on its presenter: `PostPresenter::articleSchema()`, `ProjectPresenter::creativeWorkSchema()`, `PodcastPresenter::seriesSchema()` and `seriesReference()`, `EpisodePresenter::episodeSchema()`. Take site-wide references (the author from `SiteStructuredData::authorReference()`) and related records as arguments rather than building them, and use `App\Support\Seo\JsonLd` for generic shapes such as ISO durations. Page structure (which nodes a page has, breadcrumbs, collection listings) belongs to the page ViewModel.

## Inject collaborators and build presenters through from()
Presenters take the model and every collaborator (`ResponsiveImageVariants`, `Illuminate\Foundation\Vite`, the `Illuminate\Contracts\Routing\UrlGenerator` contract) through the constructor. Each presenter has one static `from(Model $model)` factory, and it is the only place that touches the container: `app()->make(self::class, ['post' => $post])`, so a collaborator can be added without changing a caller. Callers always use `XPresenter::from($model)`. Do not call `app()`, `route()`, or the `Storage`, `Vite` or `URL` facades inside presenter methods; stored-file URLs come from `ResponsiveImageVariants::url()`, and `PresenterArchitectureTest` forbids those three facades. The one remaining global read is `PodcastPresenter` reading `config('podcasts.fallback_artwork')` in a single private helper.
