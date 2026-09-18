---
paths:
  - 'resources/views/**'
---

# Views

## Use Tailwind utilities in Blade
Use Tailwind utilities directly in Blade for layout, typography, spacing, colors, responsive behavior, and interaction states. Reserve custom CSS for theme, fonts, keyframes, third-party integration, or behavior that utilities cannot express clearly.

## Reuse Blade components for repeated markup
Extract repeated utility-heavy Blade markup into reusable Blade components. Keep custom CSS for font and theme declarations, keyframes, third-party integration, or behavior that utilities cannot express clearly.

## Organize public page views under pages
Public website route views belong under resources/views/pages. Keep a single public page flat, such as pages/archive.blade.php or pages/search.blade.php. Group related multi-page features beneath pages, such as pages/blog, pages/newsletter, pages/podcast, and pages/projects. Keep reusable Blade components under components, mail views under mail, error views under errors, and Filament views under filament.
