---
paths:
  - 'app/Providers/**'
  - 'app/Listeners/**'
---

# Providers and listeners

## Give each provider one concern
`AppServiceProvider` holds app-wide policy: lazy-loading and destructive-command guards, the admin `Gate::before`, route bindings, rate limiters, URL forcing and view registration. `MonitoringServiceProvider` wires the Sentry and Nightwatch redaction callbacks from `app/Support/Monitoring`. `Filament\AdminPanelProvider` is the only provider that uses Filament (`tests/Architecture/ProviderArchitectureTest.php`), including the panel timezone; admin navigation comes from each resource's and page's `$navigationGroup` and `$navigationSort`, not a navigation builder, and render hook markup lives in Blade views under `resources/views/filament`.

## Keep the admin gate returning false
The admin `Gate::before` returns `false` for non-administrators, never `null`. There are no policies, and Filament allows any resource ability without a policy unless a before-callback denies it. `AppServiceProviderTest` checks every resource ability for a non-administrator.

## Let Laravel discover listeners
Put event listeners in `app/Listeners` with an `…Listener` suffix and a type-hinted `handle()` event, and let event discovery register them. Do not also call `Event::listen()` for them, or they run twice.
