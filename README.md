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

For listing and pagination performance checks, create an opt-in synthetic data
set with `php artisan content:scale-test seed`. It adds one podcast with 300
episodes, 100 posts, and 50 projects. Remove only those generated records with
`php artisan content:scale-test clear`. The command is not part of `db:seed`, is
guarded against production, and creates no audio or uploaded files. Compare the
same pages and filters before considering application caching; keep caching
decisions tied to measured query and response timings.

## Quality checks

```bash
composer test
composer test:types
composer test:filament
composer lint:check
npm run build
npm run test:assets
```

## Documentation

- [Design system](DESIGN.md)
- [Editorial design](docs/editorial-design.md)
- [Project art direction](docs/project-art-direction.md)
- [Architecture](docs/architecture.md)
- [Testing](docs/testing.md)
- [Operations and deployment](docs/operations.md)
- [Release process](docs/releases.md)
- [Brand assets](docs/brand-assets.md)

## Repository notes

This is the private application source for the deployed website. It is not
intended to be used as a reusable Laravel starter project or public package.
