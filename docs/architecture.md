# Architecture

The Laravel Architect is a Laravel application with server-rendered Blade pages
for the public site, a Filament admin panel at `/admin`, and SQLite for storage.
Controllers stay thin: page data comes from ViewModels, reusable content
selection from query objects, multi-step work from actions, and validation
that queries the database or calls a service from rule classes in `app/Rules`.
Commands and procedures for running the site live in
[Operations](operations.md); this section describes how the application is
built.

Class names follow one vocabulary. An action (an imperative verb with
`handle()`) makes one state change. A query only reads. A renderer or generator
returns output from the data it is given and never queries or writes: the feed,
sitemap and robots.txt renderers in `app/Support/Feeds`, `FeaturedImageGenerator`
and `OgImageGenerator`. A `…Workflow` service runs one long operation over many
records with constructor-injected collaborators and returns a report. An
integration service such as `YouTubeService` wraps an external system, and a job
is an async unit that calls an action or service.

## Sections

- [Public site and SEO](architecture/public-site-and-seo.md): ViewModels, SEO
  metadata and JSON-LD, archives and blog search, projects, social profiles,
  previews, feeds, the sitemap and `robots.txt`.
- [Publishing and content](architecture/publishing-and-content.md): when
  content is public, publishing actions and readiness, the display timezone,
  source review, slug locking, the trash, related episodes, podcast playback,
  YouTube sync and the activity log.
- [Media](architecture/media.md): uploads, responsive variants, upload limits,
  cleanup and generated OG images.
- [Newsletter](architecture/newsletter.md): sign-up, confirmation,
  unsubscribing, suppression and the Resend webhook, and sending issues.
- [Contact](architecture/contact.md): the inquiry transaction, the email job and
  abuse controls.
- [Security and HTTP](architecture/security-and-http.md): response headers,
  rate limits, signed routes, error pages, the `/up` health endpoint and
  production safeguards.
- [Admin panel](architecture/admin-panel.md): access, authorization and the
  custom pages.
- [Frontend](architecture/frontend.md): Vite, Tailwind, the site layout, Alpine
  components and Livewire on the blog.

Related guides: [Testing](testing.md), [Release process](releases.md),
[Design system](../DESIGN.md) and [Editorial design](editorial-design.md).
