---
name: livewire-development
description: "Use for any task or question involving Livewire. Activate if the user mentions Livewire, wire: directives, or Livewire-specific concepts such as wire:model, wire:click, wire:sort, or islands. Covers building components, debugging reactivity, real-time validation, loading states, migrating from Livewire 3 to 4, component formats, and performance. Do not use for React, Vue, Alpine-only, Inertia.js, or standard Laravel forms without Livewire."
license: MIT
metadata:
  author: laravel
---

# Livewire Development

## Documentation

Use Laravel Boost's `search-docs` capability for detailed Livewire 4 patterns and documentation.

## Basic Usage

### Creating Components

```bash
# Single-file component (SFC - default in v4)
# Creates: resources/views/components/⚡create-post.blade.php
php artisan make:livewire create-post

# Full-page SFC
# Creates: resources/views/pages/⚡create-post.blade.php
php artisan make:livewire pages::create-post

# Multi-file component (MFC)
# Creates: resources/views/components/⚡create-post/create-post.php
#          resources/views/components/⚡create-post/create-post.blade.php
php artisan make:livewire create-post --mfc

# Class-based component
# Creates: app/Livewire/CreatePost.php and resources/views/livewire/create-post.blade.php
php artisan make:livewire create-post --class

# Namespaced component
php artisan make:livewire Posts/CreatePost
```

Convert between formats with `php artisan livewire:convert create-post`.

### Choosing a Component Format

Always follow the project's existing conventions first. Inspect `app/Livewire/`, `resources/views/components/`, and `resources/views/livewire/` before creating a component. If the project uses a consistent format, use it even when it differs from Livewire 4's SFC default.

Also check `config/livewire.php` for `make_command.type`, `make_command.emoji`, `component_locations`, and `component_namespaces` overrides.

| Format | Flag | Class Path | View Path |
| --- | --- | --- | --- |
| Single-file (SFC) | default | — | `resources/views/components/⚡create-post.blade.php` |
| Full-page SFC | `pages::name` | — | `resources/views/pages/⚡create-post.blade.php` |
| Multi-file (MFC) | `--mfc` | `resources/views/components/⚡create-post/create-post.php` | `resources/views/components/⚡create-post/create-post.blade.php` |
| Class-based | `--class` | `app/Livewire/CreatePost.php` | `resources/views/livewire/create-post.blade.php` |
| View-based | default | — | `resources/views/components/⚡create-post.blade.php` |

The ⚡ filename prefix is configurable. Check `config/livewire.php` and include it only when `make_command.emoji` is enabled.

### Single-File Component Example

```php
<?php
use Livewire\Component;

new class extends Component {
    public int $count = 0;

    public function increment(): void
    {
        $this->count++;
    }
};
?>

<div>
    <button wire:click="increment">Count: {{ $count }}</button>
</div>
```

## Livewire 4 Specifics

### Key Changes From Livewire 3

Verify the application's installed version and existing conventions before applying these changes.

- Use `Route::livewire()` for full-page components. Configuration keys include `component_layout` and `component_placeholder`.
- `wire:model` ignores child events by default; use `wire:model.deep` for the old behavior.
- `wire:scroll` is now `wire:navigate:scroll`.
- Component tags must be properly closed.
- `wire:transition` uses the View Transitions API.
- JavaScript uses `$wire.$js.name = fn`; request and message hooks use `interceptRequest()` and `interceptMessage()`.

### New Features

- Single-file, multi-file, and view-based component formats.
- Islands with `@island` for isolated update regions.
- Async actions with `wire:click.async` or `#[Async]`.
- Deferred and bundled loading with `defer` and `lazy.bundle`.

| Feature | Usage | Purpose |
| --- | --- | --- |
| Islands | `@island(name: 'stats')` | Isolate update regions |
| Async | `wire:click.async` or `#[Async]` | Run non-blocking actions |
| Deferred | `defer` | Load after the initial render |
| Bundled | `lazy.bundle` | Load multiple components together |

### Directives

`wire:sort`, `wire:intersect`, `wire:ref`, `.renderless`, and `.preserve-scroll` are available. The `data-loading` attribute is automatically added to elements triggering network requests.

## Best Practices

- Always use `wire:key` in loops.
- Use `wire:loading` for loading states.
- Use `wire:model.live` for live updates; `wire:model` is deferred by default.
- Validate and authorize actions as if they were HTTP requests.
- Prefer Livewire and Alpine capabilities already bundled by the installed Livewire version before adding custom JavaScript.

## Configuration and JavaScript

Review `config/livewire.php` for `smart_wire_keys`, `component_locations`, `component_namespaces`, `make_command`, and `csp_safe`. Livewire 4 includes Alpine; do not include Alpine separately without a project-specific reason.

## Testing

Use Pest and the project's established Livewire helper. In this project, prefer Filament's `livewire()` helper for Filament components and Livewire's testing helper for standalone components.

```php
livewire(Counter::class)
    ->assertSet('count', 0)
    ->call('increment')
    ->assertSet('count', 1);
```

## Verification

1. Check the browser console for JavaScript errors.
2. Confirm Livewire requests return HTTP 200 in the Network tab.
3. Ensure every `@foreach` loop has a stable `wire:key`.

## Common Pitfalls

- Missing `wire:key` in loops causes unexpected re-renders.
- `wire:model` is deferred; use `wire:model.live` for real-time updates.
- Unclosed component tags cause Livewire 4 syntax errors.
- Deprecated configuration keys or JavaScript hooks may silently fail.
- Including Alpine separately can duplicate the Alpine runtime.
