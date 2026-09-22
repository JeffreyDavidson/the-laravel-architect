# Editorial design

Use this guide when preparing a blog post, its artwork, or a podcast episode.
The shared palette, typography, controls, and theme behavior live in
[DESIGN.md](../DESIGN.md). This guide records current presentation behavior and
gives authoring recommendations; recommended image sizes are not upload validation
requirements. Individual posts need a short brief, not a separate design system.

## Article structure and reading experience

The article title is the page's H1. Start body sections with H2, and use H3 for
subsections. Lead with the problem or lesson and give readers a useful result
early. Use an excerpt that explains the article independently of its title;
it also appears in listings. Keep categories broad and tags specific.

Current article layout:

| Element | Implemented treatment |
| --- | --- |
| Header | Centered 72rem maximum container; responsive page gutters |
| Title | Maximum 20 characters of line width (`20ch`); 2.25rem, 3rem, then 3.75rem type |
| Excerpt | Maximum `68ch`; 1.125rem, then 1.25rem type |
| Metadata | Author, publication date or draft-preview label, estimated reading time |
| Featured artwork | Wider 80rem maximum container; 3:2 frame |
| Reading area | Maximum `70ch`, rendered with the shared large Markdown prose style |
| Desktop contents | Sticky 13rem sidebar alongside the prose at the large breakpoint |
| Mobile contents | Expandable “On this page” section above the prose |
| End matter | Tags, author information, then related articles when available |

The contents list is generated from H2 headings with IDs and appears only when
there are at least two. Keep headings meaningful outside their surrounding
paragraphs. Preserve anchor offsets so the fixed header does not hide the target.
The reading-progress line is decorative and respects reduced motion.

For code, use fenced Markdown with a language identifier. Keep lines reasonably
short and show only what supports the explanation. The site supplies syntax
highlighting and copy controls; code blocks remain dark in both themes. Inline
code has a separate theme-aware treatment. Keep prose useful even if highlighting
fails or JavaScript is unavailable.

Use ordinary Markdown rather than inline HTML for layout: the shared renderer
strips raw HTML and rejects unsafe link schemes. Give meaningful inline images
descriptive alt text, and follow them with a normal paragraph caption when context
is needed. Put the explanation of a diagram in the article as well as the image.

## Blog artwork

Choose an image that expresses the particular article: a relevant scene, a
focused technical illustration, or a screenshot when the interface itself is the
subject. Use the site's blue/graphite identity as a starting point for original
illustrations, with restrained secondary colors. Real screenshots retain their
product's colors. Avoid repeating one generic laptop or elephant composition for
every topic. Keep the elephant as an intentional identity element when relevant.

The title, category, and metadata already appear as HTML. For new commissioned or
generated artwork, prefer a composition without baked-in headlines, badges, tiny
code, or decorative interface text. Existing generated title cards are a separate
legacy treatment, not a requirement for new artwork.

| Placement | Frame | Image behavior |
| --- | --- | --- |
| Article hero | 3:2 | Centered `object-cover` crop |
| Standard blog card | 3:2 | Centered `object-cover` crop |
| Editorial blog card | 4:3 | Centered `object-cover` crop |
| Related article card | 3:2 | Centered `object-cover` crop |

A **1500 × 1000** master is a practical recommendation for new blog artwork:
it matches the main 3:2 frame and fits within the upload optimizer's 1600px limit.
Keep the essential subject away from the edges and check a centered 4:3 crop.
No custom focal-point field is currently supplied by the artwork component.
For a diagram whose labels must all remain visible, use an inline content image
and explanatory prose rather than depending on a cropped featured image.

The shared featured-artwork component currently uses empty alt text because
the adjacent article title supplies context. Consequently, featured artwork
must not contain the only copy of essential information.

## Uploads, fallbacks, and social images

Upload featured artwork through **Posts → Media & Metadata** in Filament.
The shared uploader accepts images up to 10 MB, converts them to WebP at quality
82, and limits each side to 1600px without intentionally enlarging small images.
Saving a changed image path triggers responsive variant generation through the
post observer. Eligible widths are 640 and 1280px; smaller sources are not upscaled.

Some existing post slugs have bundled artwork with 384/768/1280px variants.
Uploaded artwork supplies the image source when present; bundled artwork serves
the established fallback cases. A post with neither source has no artwork element.
Use Filament for new content rather than adding more slug-specific template cases.
Preview the resulting responsive image after replacing artwork on an existing post.

Two existing server generators produce 1200 × 630 graphics:

- `FeaturedImageGenerator` supplies category-themed post images with programmatic
  graphics and text; it is not an AI image generator. Its wide output is cropped
  in the article's 3:2 and 4:3 frames, so inspect it before use.
- `OgImageGenerator` produces separate social preview graphics. A social preview
  is a different deliverable from the editorial image; inspect the actual SEO
  configuration and resulting share image instead of assuming the hero crop is used.

Use [the media operations guide](operations.md) if responsive variants need repair.
Do not run bulk regeneration just to create artwork for one article.

## Podcast pages

Reuse the podcast's square cover and identity color. Episode pages pair it with
the episode code, date, duration when available, title, and description, followed
by the player, show notes, transcript when present, and episode navigation.
The cover uses a square crop; do not reuse a wide blog image without preparing
and inspecting that crop.

Show notes should stand alone: summarize the discussion, group useful links,
and explain references. Transcripts belong in the existing expandable section;
preserve its search controls and status feedback. Use real transcript content,
and avoid presenting an editorial summary as a verbatim transcript. Keep player
controls usable with the keyboard and preserve readable contrast for each
podcast's accent in both themes.

## Site identity and supporting artwork

The homepage hero is a responsive identity image rather than an article image.
Prepare separate desktop and mobile compositions, with the focal subject kept
inside the shared safe area so the responsive crops remain intentional. Keep
headlines and calls to action in HTML; the hero image should provide atmosphere
and a clear visual anchor without competing with the copy. Check the image with
the site's dark and light theme treatments, including the decorative blueprint
and steam layers that are rendered separately from the source artwork.

The about page portrait is a personal identity asset. Use a clean, recognizable
portrait with a simple background and enough breathing room for the responsive
square crop. Preserve the subject's face and shoulders in the center-safe area;
the page uses 320 and 640px variants, so inspect both the page-sized image and
its smaller rendering.

Podcast covers are square identity assets reused on the podcast index, show
page, episode page, and compact player treatments. Keep the title or mark bold
enough to survive those smaller contexts, avoid fine print, and confirm that
the cover remains legible alongside the show's accent color in both themes. Do
not use a wide blog hero as a podcast cover without preparing a dedicated square
composition.

YouTube thumbnails are external artwork shown in compact cards on the
homepage. Favor one obvious subject, strong contrast, and a composition that
still reads when the card is narrow. Treat the thumbnail as supporting content:
the card's HTML title remains the authoritative text, so do not rely on tiny
thumbnail copy to communicate the video.

The site also uses a small set of bundled identity assets, including the
elephant companion mark and error-page artwork. Keep these assets consistent
with the shared brand palette and do not substitute editorial artwork for them.
When creating a new reusable identity asset, document its intended placement,
minimum useful size, crop behavior, and light/dark theme treatment here before
adding another one-off template fallback.

## Accessibility, provenance, and delivery

Describe meaningful inline images with specific alt text that communicates the
image's purpose, not its file name. Use empty alt text only for decorative
images whose surrounding content already provides the meaning. Put important
diagram labels and explanations in the article text as well as in the image;
featured artwork and thumbnails must never be the only place essential copy
appears. Captions should add context rather than repeat the alt text.

Record the source and usage rights for commissioned, generated, stock, and
third-party artwork in the content notes or asset record. Remove private data
from screenshots before upload, and retain the original reference or prompt
when it is needed to reproduce a generated identity asset. Do not treat an
external thumbnail as a permanent local brand asset without an approved source
and replacement plan.

Prefer the existing responsive WebP pipeline over manually adding multiple
copies of an upload. Keep the source under 10 MB and within the 1600px optimizer
limit; the application generates eligible 640px and 1280px variants without
upscaling smaller sources. Use lazy loading for below-the-fold content, retain
the browser's intrinsic aspect ratio to avoid layout shift, and reserve eager
loading for the image that establishes the first viewport. Check the network
request and rendered size when an image is changed rather than assuming the
largest source is always used.

## Reusable brief

Copy this into the task or content notes when commissioning a post and artwork:

```text
Working title:
Audience and problem:
Main lesson or outcome:
Category and tags:
Excerpt:
H2 outline:
Code examples or diagrams needed:
Artwork subject and why it fits:
Medium: illustration / photo / screenshot
Required real references:
Elements to preserve or exclude:
Composition: 3:2 master; essential subject also survives a centered 4:3 crop
Image description / inline alt text where applicable:
Social preview requirements:
```

Suggested artwork prompt structure:

> Create an editorial illustration for “[title]” about [specific lesson]. Show
> [concrete subject] through [visual idea]. Use [palette appropriate to the topic
> and site] with a clear focal point and room around the subject. Compose for 3:2
> and a centered 4:3 crop. Keep titles and explanatory text in the article.
> Preserve [reference details]; exclude [unwanted elements].

## Review before publishing

- Read the title and excerpt in a listing, then inspect the full article.
- Check both artwork crops, mobile layout, both themes, and thumbnail legibility.
- Verify heading links, code copy controls, long code lines, tags, and related cards.
- Confirm screenshots and examples contain no private information.
- Review alt text, captions, licensing/source notes, and the absence of baked-in
  essential copy.
- Verify the image and its responsive variants load in the intended environment.
- Check the actual social preview separately from the article artwork.

## Implementation references

- [Article template](../resources/views/pages/blog/show.blade.php)
- [Blog cards](../resources/views/components/blog-card.blade.php)
- [Featured artwork and fallbacks](../resources/views/components/post-artwork.blade.php)
- [Markdown rendering](../resources/views/components/markdown.blade.php)
- [Article navigation and code controls](../resources/js/pages/blog.js)
- [Post form](../app/Filament/Resources/Posts/Schemas/PostForm.php)
- [Episode template](../resources/views/pages/podcast/episode.blade.php)
- [Transcript component](../resources/views/components/podcast/transcript.blade.php)
- [Upload processing](../app/Services/ImageUploadOptimizer.php)
- [Responsive variants](../app/Services/ResponsiveImageVariants.php)

For portfolio images, use [project art direction](project-art-direction.md).
