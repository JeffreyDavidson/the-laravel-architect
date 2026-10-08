# The Laravel Architect

The source repository for The Laravel Architect website, blog, podcast archive,
portfolio, and newsletter.

## Stack

- Laravel 13
- PHP 8.5
- Blade, Livewire, Tailwind CSS, and Vite
- Filament 5
- SQLite
- Laravel Forge deployment

## Local development

This repository is primarily maintained locally. To set up a development
environment:

```bash
composer setup
composer dev
```

The setup command installs dependencies, creates the local environment file,
generates an application key, runs migrations, and builds frontend assets.
Use it only for a fresh environment, not to restart an existing installation.

Laravel Herd serves the site. Secure the site in Herd and keep `APP_URL` set to
its HTTPS address. `composer dev` starts the database queue listener, logs, and
Vite only; it does not start a second HTTP server. Leave that command running
while developing and stop it with Ctrl+C when finished.

To create a local administrator on a fresh environment:

```bash
php artisan make:filament-user --panel=admin --email=admin@example.test
php artisan db:seed
php artisan storage:link
```

The default seeder manages the administrator account and does not recreate
editorial content. For realistic local content, import a reviewed public-content
archive with `php artisan content:import-public /absolute/path/to/archive.json`.
Import replaces the target's current public content, so use it on a fresh or
backed-up local database. Archives contain public text and metadata, not
uploaded media; do not copy a production database to local development.

For listing and pagination performance checks, use the opt-in synthetic data set
described in [Local content and scale checks](docs/testing.md#local-content-and-scale-checks).

## Quality checks

```bash
composer check
```

`composer check` runs every gate in CI order. The individual scripts are listed in [docs/testing.md](docs/testing.md#composer-scripts).

## Documentation

- [Design system](DESIGN.md)
- [Editorial design](docs/editorial-design.md)
- [Voice guide](docs/voice.md)
- [Project art direction](docs/project-art-direction.md)
- [Portfolio presentation](docs/portfolio-presentation.md)
- [Project description template](docs/project-description-template.md)
- [Architecture](docs/architecture.md), with detail pages in `docs/architecture/`
- [Layers: where code goes](docs/architecture/layers.md)
- [Testing](docs/testing.md)
- [Operations and deployment](docs/operations.md), with detail pages and
  runbooks in `docs/operations/`
- [Release process](docs/releases.md)
- [Brand assets](docs/brand-assets.md)
- [YouTube brand kit](docs/youtube-brand-kit.md)
- [History](docs/history/): records of finished operational changes

## Repository notes

This is the private application source for the deployed website. It is not
intended to be used as a reusable Laravel starter project or public package.
