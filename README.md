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

To create a local administrator on a fresh environment:

```bash
php artisan make:filament-user --panel=admin --email=admin@example.test
php artisan db:seed
php artisan storage:link
```

The seeder manages the administrator account and does not recreate editorial
content. Restore content from a validated backup or import a public content
archive when preparing another environment.

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

- [Architecture](docs/architecture.md)
- [Testing](docs/testing.md)
- [Operations and deployment](docs/operations.md)
- [Release process](docs/releases.md)
- [Brand assets](docs/brand-assets.md)

## Repository notes

This is the private application source for the deployed website. It is not
intended to be used as a reusable Laravel starter project or public package.
