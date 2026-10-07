---
paths:
  - 'app/Presenters/**'
---

# Presenters

## Keep presenters focused on model presentation
Presenters wrap one model and expose reusable display formatting only. They must not fetch records, aggregate data, or perform application operations.

## Inject collaborators and build presenters through from()
Presenters take the model and every collaborator (`ResponsiveImageVariants`, `Illuminate\Foundation\Vite`, the `Illuminate\Contracts\Routing\UrlGenerator` contract) through the constructor. Each presenter has one static `from(Model $model)` factory, and it is the only place that touches the container: `app()->make(self::class, ['post' => $post])`, so a collaborator can be added without changing a caller. Callers always use `XPresenter::from($model)`. Do not call `app()`, `route()`, or the `Storage`, `Vite` or `URL` facades inside presenter methods; stored-file URLs come from `ResponsiveImageVariants::url()`. The one remaining global read is `PodcastPresenter` reading `config('podcasts.fallback_artwork')` in a single private helper.
