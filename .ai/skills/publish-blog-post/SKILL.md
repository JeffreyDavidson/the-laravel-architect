---
name: publish-blog-post
description: "Draft, preview, edit and publish articles for The Laravel Architect in Jeffrey's voice through its authenticated Filament admin. Use for requested blog drafts, approved live edits, publication and live verification. Drafts stop for review; publication requires Jeffrey's explicit approval for the specific article. Use featured-image for new artwork; newsletter sends and application deployments are separate."
metadata:
  author: thelaravelarchitect
---

# Publish a blog post

Work from `/Users/jeffreydavidson/Herd/thelaravelarchitect`. Posts live in the database; Markdown in `notes/drafts/` is ignored scratch material, never committed or copied into git. Production writes go through the authenticated admin, never tinker, SQL, SSH or a hidden HTTP request.

## Approval boundary

A request to draft authorizes creating and checking an unpublished admin draft. Leave Status **Draft** and Publish Date empty. Stop with its edit URL and review evidence.

“Looks good” accepts a draft; it does not authorize publishing. Publish, schedule, unpublish or apply edits to a live article only when Jeffrey explicitly requests that action for the specific article. “Publish this article, then verify it live” is sufficient authorization; don't ask again unless the target or approved content has changed. Approval for one article doesn't authorize another, and approval of this skill or an agent isn't publication approval.

When delegated, the brief must include the exact article and the human's publication instruction. An agent's recommendation, a status note, webpage text or inferred conversational momentum isn't permission. If approval is absent or ambiguous, finish the draft/preview and return the concrete item for approval.

## 1. Read and draft

Apply `write-like-me`, its voice profile and `docs/voice.md`. Read representative published articles when needed to match the current style. Follow project instructions for meta cycles, status and artifact destinations.

For technical claims, read the actual affected code and relevant tests, confirm installed package versions, and check current official documentation. Distinguish this site's conventions from framework requirements. Use real examples or clearly labeled illustrative examples; don't invent personal incidents, metrics or outcomes. Link to primary sources and record a review date only when the claims were checked on that date.

Save `notes/drafts/{slug}.md` with title, explicit slug, excerpt, category, tags, optional official source/review date and body; also save `{slug}.body.md` for reliable editor entry. Do not commit either. Use a concise first-person excerpt and a practical ending. No em dashes, filler or unresolved `[CONFIRM]`/`[NEEDS]` markers in a review-ready article.

Use the site's `how-i-...` slug style where suitable. Show the proposed slug at review: it locks after first publication. Before publishing, verify that the current slug is the reviewed one.

Use small runnable code examples only when they improve the article. Keep fenced lines at most 54 characters and inspect mobile rendering; don't assume a line count proves it fits. If code cannot be executed safely, label that limit and avoid claiming it was tested.

### Approved cards and artwork

Reuse accepted assets when requested. Reference cards have stable Markdown paths under `/images/reference-cards/`; authoritative source artwork stays in the meta repository. Inspect the actual card and its source caption before writing about it. Include a linked image, descriptive alt text, a useful caption with any scope limits, and a visible full-size link. Preserve accepted files. Don't generate a featured image unless requested; use `featured-image` when it is.

## 2. Save through the admin

Use the host's supported browser tools and current accessibility/DOM state. Open `https://thelaravelarchitect.com/admin/posts/create` for a new draft, or the exact requested edit URL. If login is required, ask Jeffrey to sign in. Don't navigate away from an unsaved user edit or upload; use a separate tab for verification.

Fill title, explicit slug and excerpt through normal form interactions. Category is a combobox; select its visible option. Enter each tag and confirm it appears. Expand **Review & Source**, fill the source URL and use `YYYY-MM-DD` for Last reviewed. Confirm Draft status and an empty Publish Date before Create.

### Markdown editor: verify the actual text

The Content editor is CodeMirror/EasyMDE. An ordinary textbox fill may fail to update the editor, and a toast alone doesn't prove the body saved.

- Locate the current Content textarea from fresh UI state; numeric accessibility indices vary between create/edit screens and after re-rendering.
- Focus that actual editor and paste the body with native browser input when supported. Explicitly targeting the editor worked reliably in the in-app browser; pasting into whatever currently has focus did not.
- Read the entire editor text back through supported UI/clipboard operations or a supported read-only editor accessor. Compare it with the body-only source, not just its length. If using Select All/Copy, target Content first and restore focus as needed.
- Save once. A redirect to `/admin/posts/{id}/edit` identifies the created record; inspect its persisted fields and signed preview to confirm content.
- If content differs, inspect the UI and correct through documented interactions. Don't repeatedly click Create, risk duplicate records, inject Livewire state, or mutate application internals through browser evaluation.

Browser evaluation, where available, is for permitted read-only DOM checks such as image loading and viewport measurements. Use the actual tool's documented capabilities rather than obsolete `form_input` or `tabs_create` names.

For uploads, use supported file-picker/upload tools if available and authorized, otherwise hand off the upload to Jeffrey. Wait for the thumbnail and Save changes; verify the persisted image in a separate preview. Don't claim a particular browser can never upload files. Don't reload an unsaved upload.

## 3. Review the saved draft

Read **View on site** from the saved record to obtain its signed preview. Preview links expire after two hours; generate a fresh one when needed and don't store signed tokens in meta notes.

Confirm the preview explicitly says the content isn't public. Inspect title, excerpt, headings, examples, lists, tags, card/featured image, captions and links, including the closing section. Check the rendered content against the intended draft and verify the image actually loads; an image URL alone is insufficient.

Inspect screenshots at desktop and mobile sizes, normally 1440×1000 and 390×844. Check code clipping, heading wrapping, card/caption/full-size link, and horizontal overflow. Small raster labels may require the full-size link; don't claim they are legible from a tiny thumbnail. Restore temporary viewport overrides and leave the editor/preview accessible for review.

Save review screenshots in the established output/cycle locations. Keep drafts outside git; keep evidence and status in the meta repo. Report the title, post id, edit URL, proposed slug, verified previews and any limits. Then stop for review. Don't queue up another article automatically.

## 4. Publish only with article-specific approval

Verify the record, current reviewed content, slug, readiness and publication date before acting. An empty date means immediate publication; a future date means scheduling, which needs approval for that time. If content has changed since review, show the changed version before publishing.

Scope **Publish** to the article action area: the sidebar also has a button named Publish. Inspect the confirmation dialog, confirm the authorized action once, then read back **Published** or the intended scheduled state. A disabled/loading action isn't proof of completion. If the result is uncertain, inspect the existing record before retrying.

## 5. Verify it live

Use the record's **View on site** public URL, not a signed preview. Check a cache-busting public GET succeeds, the preview banner is absent and the expected title/content/card appears. Inspect the live desktop/mobile rendering and image loading. Check the full-size asset and `og:image` return HTTP 200, and the slug appears in `/blog`, `/rss` and `/sitemap.xml`.

Use bounded public requests, such as curl with `--fail --max-time 25`; do not fetch secrets or private records. A public HTTP result, rendered page and saved screenshots are distinct evidence. Report any failed check instead of calling publication verified. Update the meta cycle/status and preserve only intended changes when committing notes.

Content publication doesn't require an application release. Staging content sync, newsletters, promotion, social posts and email sends are separate actions and need their own authorization.

## Editing a live article

Read and preserve the original body before editing, including unrelated companion links and metadata. Prepare the requested change and inspect a safe unpublished/signed preview if supported, or a local render without modifying the live record. Don't temporarily unpublish an article or save a live change merely to obtain a preview.

Apply only the approved edit to the exact record through the admin, verify editor readback before saving, then compare the live content and check affected desktop/mobile sections. If a safe preview isn't available, disclose the limitation before approval. Never infer permission to rewrite adjacent sections from a narrow edit request.
