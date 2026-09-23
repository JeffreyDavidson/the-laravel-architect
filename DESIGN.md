---
name: The Laravel Architect
description: Blue, graphite, and IBM Plex connect the public website with its editorial studio.
colors:
  brand-600: "#4a7fbf"
  brand-action: "#3f6fa8"
  brand-action-hover: "#345f91"
  brand-300: "#7eb0dc"
  brand-200: "#a8cce8"
  brand-50: "#eaf3fa"
  brand-950: "#0d0f12"
  accent-500: "#d4808f"
  light-page: "#f6f8fa"
  light-surface: "#ffffff"
  light-inset: "#f0f3f6"
  light-ink: "#1f2328"
  light-muted: "#424a53"
  light-subtle: "#656d76"
  light-line: "#d0d7de"
  light-link: "#245b91"
  dark-page: "#0d1117"
  dark-studio-surface: "#111820"
  dark-raised: "#161b22"
  dark-studio-ink: "#eef4fa"
  dark-muted: "#c9d1d9"
  dark-muted-strong: "#d2deee"
  dark-studio-subtle: "#a8b3c2"
  dark-studio-line: "#263241"
  dark-studio-line-strong: "#42556d"
  dark-accent-bright: "#9fc0e5"
  studio-success-light: "#14733b"
  studio-success-dark: "#4ade80"
  studio-warning-light: "#855400"
  studio-warning-dark: "#fbbf24"
  studio-subheading-muted: "rgba(226, 232, 240, 0.64)"
  studio-success-halo: "rgba(52, 211, 153, 0.12)"
  studio-neutral-hover: "rgba(148, 163, 184, 0.2)"
  studio-success-halo-strong: "rgba(16, 185, 129, 0.13)"
  studio-warning-border: "rgba(245, 158, 11, 0.25)"
  studio-warning-muted: "rgba(245, 158, 11, 0.08)"
  studio-warning-soft: "rgba(245, 158, 11, 0.16)"
  studio-warning-strong: "rgba(245, 158, 11, 0.22)"
  studio-success-strong: "rgba(16, 185, 129, 0.12)"
  studio-rose-soft: "rgba(199, 75, 122, 0.1)"
  studio-success-soft: "rgba(16, 185, 129, 0.1)"
  studio-warning-glow: "rgba(245, 158, 11, 0.1)"
  studio-success-surface: "rgba(16, 185, 129, 0.14)"
  studio-warning-surface: "rgba(245, 158, 11, 0.14)"
  studio-nav-active: "#2b5f97"
  home-copy-shadow: "rgba(0, 0, 0, 0.28)"
  studio-blue-mist: "rgba(122, 169, 224, 0.38)"
  studio-neutral-soft: "rgba(148, 163, 184, 0.26)"
  studio-neutral-strong: "rgba(148, 163, 184, 0.48)"
  studio-blue-lift: "rgba(122, 169, 224, 0.72)"
  studio-accent-soft: "rgba(91, 145, 206, 0.14)"
  studio-accent-strong: "rgba(91, 145, 206, 0.2)"
  studio-focus-wash: "#d9ebff"
  studio-row-hover: "rgba(91, 145, 206, 0.055)"
  studio-row-selected: "rgba(91, 145, 206, 0.1)"
  auth-field-border: "#8c959f"
  auth-field-hover-border: "#697586"
typography:
  body:
    fontFamily: "IBM Plex Sans, ui-sans-serif, system-ui, sans-serif"
    fontSize: "1rem"
  code:
    fontFamily: "IBM Plex Mono, ui-monospace, monospace"
  wordmark:
    fontFamily: "Empera, serif"
    fontWeight: 400
  auth-heading:
    fontFamily: "IBM Plex Sans, ui-sans-serif, system-ui, sans-serif"
    fontSize: "1.65rem"
    letterSpacing: "-0.035em"
  meta:
    fontSize: "0.625rem"
    lineHeight: 1.4
  meta-sm:
    fontSize: "0.75rem"
    lineHeight: 1.4
  proof-value:
    fontSize: "2rem"
  studio-field-note:
    fontSize: "0.8125rem"
  action-label:
    fontSize: "0.875rem"
  error-display-min:
    fontSize: "2.5rem"
  studio-subtitle:
    fontSize: "1.125rem"
  home-display:
    fontSize: "4.5rem"
  home-cta:
    fontSize: "0.9375rem"
  studio-kicker:
    fontSize: "0.68rem"
  error-display-fluid:
    fontSize: "1.85rem"
  studio-body:
    fontSize: "0.95rem"
  studio-description:
    fontSize: "0.8rem"
  studio-support:
    fontSize: "0.9rem"
  error-section:
    fontSize: "1.75rem"
  studio-caption:
    fontSize: "0.7rem"
  studio-compact:
    fontSize: "0.88rem"
  studio-heading:
    fontSize: "1.35rem"
  studio-dashboard-max:
    fontSize: "3rem"
  studio-hero-max:
    fontSize: "2.55rem"
  studio-widget-title:
    fontSize: "1.08rem"
  studio-metadata:
    fontSize: "0.92rem"
  studio-label:
    fontSize: "0.82rem"
rounded:
  code: "0.5rem"
  public-button: "0.75rem"
  public-card: "1rem"
  auth-card: "1rem"
  theme-switcher: "0.65rem"
  compact: "0.375rem"
  code-inline: "4px"
  micro-control: "3px"
  status-pill: "999px"
  status-mark: "9999px"
  studio-panel: "0.8rem"
  studio-card: "0.85rem"
  studio-widget: "0.9rem"
  studio-dot: "0.45rem"
spacing:
  button-md-x: "1.5rem"
  button-md-y: "0.75rem"
  page-gutter-mobile: "1rem"
  page-gutter-sm: "1.5rem"
  page-gutter-lg: "2rem"
components:
  button-primary:
    backgroundColor: "{colors.brand-action}"
    textColor: "{colors.light-surface}"
    rounded: "{rounded.public-button}"
    padding: "0.75rem 1.5rem"
  button-primary-hover:
    backgroundColor: "{colors.brand-action-hover}"
  auth-theme-active:
    backgroundColor: "{colors.brand-action}"
    textColor: "{colors.light-surface}"
    rounded: "0"
    width: "2.25rem"
    height: "2.25rem"
---

# Design System: The Laravel Architect

## Overview

The public site presents Jeffrey Davidson's Laravel development, architecture,
writing, projects, and podcast. Its visual identity pairs a blue elephant with
technical typography, graphite surfaces, generous public-page spacing, and
restrained blue accents. Coffee and architectural imagery add personality.
The admin panel carries the same identity into a compact editorial studio.

This document captures the implemented system and the refinements already
accepted for this project. It is a reference for extending existing screens.
The token names above describe observed roles. The public stylesheet and admin theme
expose semantic CSS variables for the shared roles below; use those tokens before
adding a new literal value.

Implementation references:

- [Public tokens, fonts, and motion](resources/css/app.css)
- [Filament theme and studio tokens](resources/css/filament/admin/theme.css)
- [Panel configuration and navigation](app/Providers/Filament/AdminPanelProvider.php)
- [Public layout](resources/views/components/layouts/site.blade.php)
- [Brand assets and logo provenance](docs/brand-assets.md)
- [Editorial layouts and artwork briefs](docs/editorial-design.md)
- [Project screenshot and artwork guidance](docs/project-art-direction.md)
- [Frontend build entries](vite.config.js)

Earlier local admin critiques are historical reviews, excluded from Git.
Several findings have since been addressed; consult the current implementation
before treating an observation as an open issue.

## Colors

### Primary

Architect blue is the shared identity color. Use `brand-600` for brand details;
use the darker `brand-action` and `brand-action-hover` for filled buttons with
white text. Public light-mode links use `light-link`; dark-mode accents use
lighter blues. A brand swatch and a readable action color serve different roles.

### Secondary

Muted rose supports occasional highlights. In the studio, green indicates
success or published content, amber indicates review or attention, blue
indicates scheduled content, and neutral text represents drafts. Pair status
color with readable labels. Filament also supplies its configured semantic
palettes; preserve those native states.

The studio also uses translucent semantic surfaces derived from those roles:
amber for review alerts, blue for inquiry and scheduled actions, green for
healthy or published states, and rose for secondary action icons. These are
intentional admin-only utility treatments and should remain behind the
`--tla-*` semantic tokens rather than becoming new public-site colors.

### Neutral

Use semantic roles for pages, surfaces, inset controls, text, and borders.
The public stylesheet exposes `--bg-*`, `--text-*`, and `--border-primary`,
plus Tailwind `surface-*` colors. The studio exposes `--tla-*` tokens.

| Role | Public source | Studio source |
| --- | --- | --- |
| Page | Layout utilities and `--bg-primary` | `--tla-page` |
| Card | Component utilities and `--bg-card` | `--tla-surface` |
| Raised surface | `surface-elevated` | `--tla-surface-raised` |
| Main text | `--text-primary` | `--tla-ink` |
| Supporting text | `--text-secondary` | `--tla-muted` |
| Metadata | `--text-tertiary` | `--tla-subtle` |
| Border | `--border-primary` / `surface-border` | `--tla-line` |
| Strong border | `surface-border-strong` | `--tla-line-strong` |

These are corresponding roles, not interchangeable values. For example, the
public layout uses `brand-950` as its dark body background, while the admin page
uses `dark-page`. Public dark cards can be translucent; studio panels use solid
surfaces. Preserve these intentional differences.

Both themes need readable fields, borders, muted text, validation, and focus
indicators. Use the theme-specific tokens rather than carrying a dark-mode
color unchanged into light mode.

## Typography

- **IBM Plex Sans:** body copy, headings, controls, public navigation, and the
  Filament interface. It is locally bundled as a variable font.
- **IBM Plex Mono:** code, technical metadata, and selected small labels.
  The public bundle includes weights 400, 600, and 700; the admin theme declares 400.
- **Empera:** the public identity wordmark. Keep this display treatment within
  the branding rather than extending it to forms or article paragraphs.

Public headings use a clear, semibold hierarchy and balanced wrapping.
The home hero steps from 3rem to 3.75rem and then 4.5rem at wider breakpoints.
The layout also applies home-heading line-height and tracking overrides, so
check the cascade before copying the hero's local classes.

Article cards use smaller responsive headings and readable excerpts. Technical
eyebrows use monospaced, uppercase text with generous tracking; regular controls
and body copy use normal casing. Keep tiny metadata styles confined to metadata.

The studio uses compact headings, short labels, and clear supporting text.
The dashboard welcome heading's final rules are semibold, balanced, and use
normal line height. Check later overrides in the admin stylesheet before
reusing values from an earlier block.

Admin utility text uses the compact scale already present in the theme: labels
around `0.7rem`–`0.8rem`, supporting copy around `0.78rem`–`0.95rem`, and
dashboard headings from `1.08rem` up to `3rem` responsively. These values are
for dense studio UI only; public content should use the surrounding page
scale.

## Layout

Public header and home content use a centered `max-w-7xl` container (80rem), with
gutters of 1rem, 1.5rem at `sm`, and 2rem at `lg`. Reading surfaces may be narrower;
reuse the surrounding page's content width. The home hero uses separate desktop
and mobile artwork crops, keeping its copy readable against a dark backdrop in
both themes. See [home](resources/views/pages/home.blade.php).

Use Tailwind's existing responsive utilities and the spacing of sibling
components. Public pages have room for illustration and section rhythm;
admin pages prioritize scanning, editing, and navigation.

The studio shell combines a topbar, collapsible sidebar, and full-width content.
Navigation groups are Publish, Library, Audience, and Operations. Keep Profile
inside that shell; authentication uses the centered simple layout.

The dashboard presents publishing status, attention items, creation actions,
and recent activity. Keep reporting in Insights rather than repeating metrics
throughout the dashboard. Admin content gutters use `clamp(1rem, 3vw, 3rem)`;
below 768px they become 1rem. Dashboard widgets use container queries: the
pipeline changes from four columns to two at 64rem, the hero stacks at 56rem,
and smaller controls stack at 40rem. These widths refer to the widget container.

On narrow screens, resource tables scroll within their content container.
Preserve accessible row actions and readable columns without widening the page.

## Elevation & Depth

Depth primarily comes from contrasting surfaces and fine borders. Dark studio
panels, tables, and floating menus use restrained solid surfaces with no shadow.
Light-mode auth cards and menus use soft shadows; these are deliberate
exceptions, not a reason to add shadows to every card.

The current light auth shadow is `0 18px 45px rgba(31, 35, 40, 0.08)`.
Light floating panels use `0 18px 40px rgba(31, 35, 40, 0.12)`.
Public muted cards gain a small shadow on hover.

Dropdown visibility depends on ancestor clipping and containing blocks as well
as z-index. The topbar must not regain the backdrop filter that trapped fixed
dropdowns. Preserve Filament's positioning and Alpine visibility contracts,
including `x-cloak`. Verify menus after SPA navigation and on Profile as well
as the dashboard.

## Shapes

The system uses softly rounded rectangles, fine borders, and circular status
marks. Public buttons default to a 0.75rem radius; public cards and auth cards
use 1rem. The home hero deliberately uses a smaller button radius. Code blocks
use 0.5rem. Admin sections and controls have their own slightly tighter radii;
reuse the current theme instead of applying one radius universally.

The auth appearance selector is one segmented control. Its outer border is
rounded and clips the segments; each inner button has zero radius. The active
segment fills its cell in blue with a white icon. Do not reintroduce the rounded,
floating active tile that conflicted with the enclosing outline.

Admin controls use tighter radii than public cards: roughly `0.62rem`–`0.9rem`
for dashboard controls and panels, `0.65rem` for the appearance selector, and
fully circular marks only for status dots, avatars, and timeline markers.

## Components

### Identity and imagery

Keep the original elephant-and-coffee badge as the primary identity and footer
mark. Use the companion elephant head for the header and admin branding; consult
the brand-asset guide for favicon variants. Preserve aspect ratios and transparent
edges. Reuse current hero artwork and real project imagery. Local synthetic test
content is not production copy or a source of business claims.

### Buttons and cards

Use [the public button component](resources/views/components/button.blade.php)
for primary and outline actions. It supports `sm`, `md`, and `lg` sizes, an `href`
for links, and visible keyboard focus. The medium size uses the frontmatter's
button padding. Filament actions retain native loading, disabled, and validation
behavior under the admin theme.

Use [card](resources/views/components/card.blade.php) and
[muted-card](resources/views/components/public/muted-card.blade.php) for their
existing roles. Article listings use [blog-card](resources/views/components/blog-card.blade.php)
with artwork, metadata, title, and excerpt; editorial and list variants differ.
Do not infer that all article cards are text-only from an older stylesheet comment.

### Fields and authentication

Retain visible labels, field-level errors, and a distinct field surface.
Light auth inputs have a tinted background and a visible neutral border;
dark inputs use a recessed surface. Focus has a blue border and outline.
The password reveal icon shares the input surface without a vertical divider
on simple auth pages. It remains a native interactive control.

The login card uses the companion mark, Sign in heading, Private studio access
label, form, and appearance control. The sign-in button has a minimum height
of 2.75rem. Do not expose internal diagnostics in this flow.

### Appearance controls

The public header currently toggles light/dark and initializes from the saved
preference or OS preference before first paint. The admin provides Filament's
native Light / Dark / System selector, also reused beneath the login form.
These are different current controls; do not claim the public header already
has a three-way selector. See [public toggle](resources/views/components/site/theme-toggle.blade.php)
and [auth selector](resources/views/filament/auth/theme-switcher.blade.php).

### Navigation and icons

Public navigation uses a small blue underline for hover, focus, and the current
page. Preserve the mobile menu and skip-to-content link. Studio navigation uses
a tinted active row and brand accent, with native group collapsing and global
search shortcuts.

Keep the New post action aligned with navigation icons in the collapsed sidebar.
Its plus is drawn with centered CSS strokes inside a circle, avoiding a text
glyph's baseline offset. Preserve accessible labels when visible labels disappear.

### Tags, status, and feedback

Public [tag pills](resources/views/components/tag-pill.blade.php) use a rounded
outline and small type. Studio badges combine readable status text with color.
Empty states should explain what can happen next; loading and errors should
leave the user's task understandable. Reuse native Filament feedback components.

### Code, error pages, and motion

Code blocks stay dark in both themes, use monospaced type, and reveal a copy
action on hover or keyboard focus. Inline code uses its own theme-aware tint.

Branded 404, 500, and 503 pages preserve typography, identity, and clear recovery
actions. The [server-error template](resources/views/errors/server.blade.php)
has self-contained styling so it can render when normal application services
are unavailable. Keep that independence when adjusting its appearance.

Most studio state transitions are 160ms; public navigation underlines use 180ms.
The home illustration has dedicated steam and blueprint animation. Respect
reduced-motion preferences when adding or modifying animation, following the
existing motion-reduction treatments. Keep decoration out of the interaction path.

## Do's and Don'ts

- **Do** reuse existing tokens and components before adding another visual variant.
- **Do** verify light and dark modes, keyboard focus, narrow screens, zoom, long
  labels, and empty/error states when changing a component.
- **Do** check collapsed sidebar alignment and dropdown visibility after navigation.
- **Do** use Tailwind utilities in public Blade and the Vite-backed Filament theme
  for admin overrides. Keep public and admin CSS entry points separate.
- **Do** update this document alongside intentional changes to the visual system.
- **Don't** replace the elephant identity or established fonts incidentally.
- **Don't** remove field borders until controls disappear into their background.
- **Don't** add independent rounding to the appearance selector's active segment.
- **Don't** repair dropdowns by increasing z-index without checking ancestor clipping.
- **Don't** edit generated files in `public/css/filament` or vendor files to theme
  the panel. Change the maintained sources and build through Vite.
- **Don't** treat this extracted reference as proof that every screen has passed
  an accessibility audit. Verify contrast and interactions on the actual surface.
