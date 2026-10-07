# Frontend

Public pages are Blade views styled with Tailwind and made interactive with
CSP-safe Alpine components, all compiled through Vite. The visual design system
is in [DESIGN.md](../../DESIGN.md).

## Assets and Vite

Public interaction modules live in `resources/js`, shared presentation rules
live in `resources/css`, and both are compiled through Vite.

- Compressed budgets guard the public entry points and lazy modules in CI.
- The Filament theme is a separate budgeted Vite entry loaded only by the admin
  panel.
- Editorial images that participate in the build live in `resources/images` and
  are referenced with `Vite::asset()`.
- Files that must retain a stable direct URL for browsers or third-party
  consumers, such as favicons and Filament branding, remain in `public`.
- The asset-budget check rejects unexpected files in `public/images`, so new
  images must either use the Vite pipeline or be intentionally added to the
  direct-URL allowlist.

## Styles

Public presentation styles live in Tailwind utility classes on the owning Blade
pages and components, compiled into one `resources/css/app.css` bundle. There
are no page-specific CSS entry points.

Tailwind scans only the files listed with `@source`, which cover the Blade views
and JavaScript but not PHP classes. Keep class names in Blade (search
highlighting, for example, is the `search-highlight` component) rather than
building them in PHP, or they are never generated.

Shared CSS retains fonts, theme tokens, animation keyframes, and browser
integration rules; Prism and the Filament admin theme remain separate
integrations.

## Site layout

The site layout (`components/layouts/site.blade.php`) is a full-height column
(`min-h-dvh`) whose `<main>` grows (`flex-1`), so the footer sits at the bottom
of the window on short pages such as the newsletter confirm and unsubscribe
pages instead of floating mid-screen.

## Alpine components

Public UI state uses named CSP-safe Alpine components for:

- navigation, theme controls, the about card, copy feedback and video
  activation;
- the newsletter sign-up and confirmation auto-submit (see
  [Newsletter](newsletter.md));
- the contact form's Turnstile loader;
- the homepage reveal and count-up animations;
- the blog index's live title and meta tag updates after a search;
- the blog article's code copy buttons, lazy syntax highlighting and contents
  list;
- the podcast transcript's section links, search and deep links.

JavaScript uses data attributes for behavior hooks, and article navigation
clones its styled link markup from a Blade template.

Pages without Livewire load standalone `@alpinejs/csp`; the blog uses Alpine
bundled with Livewire's CSP-safe runtime. Components register before the
selected runtime starts, so each page has exactly one Alpine instance.

All custom page behavior is an Alpine component. The only plain browser code
left is shared plumbing: the clipboard helper (`resources/js/utils/clipboard.js`)
and the Prism syntax highlighter (a vendor module the article component loads
lazily).

`resources/js/app.js` registers the site header and copy button on every page
and imports each `resources/js/pages/*` module (`about`, `home`, `contact`,
`newsletter`, `newsletter-confirm`, `blog-index`, `blog`, `podcast`) only when
its `data-*` hook is on the page. The admin panel's script is
`resources/js/filament/admin.js`.

## Livewire on the blog

The blog bundles Livewire's CSP-safe runtime through Vite only when its script
configuration is present. Filament uses the standard Livewire runtime; do not
enable `livewire.csp_safe` globally. Rebuild frontend assets after Livewire
upgrades.

The controller supplies the initial blog payload to the component once;
subsequent component updates refresh title, canonical URL, social metadata,
robots, and JSON-LD together. Pagination retains real links for visitors without
JavaScript.
