---
name: publish-blog-post
description: "Writes, previews, and publishes a blog post on this site: draft in the house voice, optional local draft for preview, then entering it into the production Filament admin through the browser pane, checking it, and verifying it after the user publishes. Use when the user asks to write a blog post, add a post to production or staging, fix or update a live post, or check that a post went live. Not for featured images (use featured-image) or newsletter sends."
metadata:
  author: thelaravelarchitect
---

# Publish a blog post

Posts live in the database and are managed in Filament. Markdown files are scratch drafts only: `notes/` is gitignored, so do not commit them or add copies to git. Never write production data from the command line; production goes through the admin. Publishing and unpublishing are the user's decisions.

## 1. Draft

Read `docs/voice.md` and follow it (no em dashes, real or composite examples only, checklist at the end). Save the draft to `notes/drafts/{slug}.md` with the fields below, plus a body-only copy `notes/drafts/{slug}.body.md`.

- Title, excerpt (one or two first-person sentences), category (Career, Laravel, or Personal), tags.
- Slug: an explicit `how-i-...` style kebab-case slug. It locks permanently once the post is published, so confirm it with the user.
- Optional source: a source URL (the official docs the post relies on) and a last-reviewed date. Only set the date to a day you actually checked the claims.
- Code in fenced blocks: keep every line to 54 characters or fewer or it clips in the article column. Pad Markdown tables so the `|` characters align inside code blocks.
- Verify package versions, tool names, and commands against the installed version or official docs before relying on them.

## 2. Optional local preview

Ask before creating local models. With approval, create a local Draft with tinker (`status` = `PublishStatus::Draft`, no `published_at`, an existing `user_id` and `category_id`, `attachTags`), then open a signed preview. Previews work for drafts and expire after 2 hours.

```bash
php artisan tinker --execute 'echo app(App\Support\Content\PreviewUrlGenerator::class)->for(App\Models\Post::findOrFail(ID));'
```

Soft-delete the local draft afterwards if the user wants it gone (`->delete()`; force delete removes files).

## 3. Enter it in production (browser pane)

The user must be signed in to the production admin inside the built-in browser pane; it does not share their other browser's session and you cannot sign in for them. Open `https://thelaravelarchitect.com/admin/posts/create`; if it shows the login page, stop and ask them to sign in there.

- Title (`form.title`), slug (`form.slug`) and excerpt: `form_input`.
- Content: `form_input` on the Content textbox. The Markdown editor is CodeMirror/EasyMDE; the Livewire form data does not always follow it. Always verify and, if needed, set the data directly, then check the length matches the draft:

```js
const wid = document.querySelector('.CodeMirror').closest('[wire\\:id]').getAttribute('wire:id');
const lw = Livewire.find(wid);
await lw.set('data.content', document.querySelector('.CodeMirror').CodeMirror.getValue());
lw.get('data.content').length
```

- Category: click the combobox, pick the option, confirm with a screenshot. Tags: type each tag and press Return.
- Source URL and Last reviewed are inside the collapsed "Review & Source" section; expand it (a stray click opens "Review" instead, which is harmless). Set the date with `form_input` using `YYYY-MM-DD`.
- Leave Status as Draft and Publish Date empty. Confirm the field values, then click Create. A redirect to `/admin/posts/{id}/edit` means it saved.

## 4. Featured image

The pane cannot attach local files. Ask the user to upload it in the pane: Media & Metadata, Browse, choose the file, wait for the thumbnail, click **Save changes**, and tell you when saved. Do not reload or navigate that tab until they say it saved: an unsaved upload is discarded by a reload (this cost two rounds before). Verify in a second tab (`tabs_create`), looking for a `.filepond--item` whose name is a `.webp`. Use the `featured-image` skill for the artwork itself.

## 5. Check the draft

In the posts table, the **View on site** action carries a fresh signed preview link; open it. Check the hero image, all headings, code blocks, lists, links, tags, and that no `[CONFIRM]` placeholder remains. Report the post id and edit URL, then wait. Do not click Publish unless the user asks you to.

## 6. After the user publishes

```bash
S=<slug>; U=https://thelaravelarchitect.com
curl -sL "$U/blog/$S?cb=$(date +%s)" -o post.html -w '%{http_code}\n'
grep -c 'Preview mode' post.html                      # 0 once live
grep -o 'og:image" content="[^"]*' post.html          # image URL; curl it for a 200
curl -s $U/rss | grep -c "$S"; curl -s $U/sitemap.xml | grep -c "$S"; curl -s $U/blog | grep -c "$S"
```

Staging only receives it from `php artisan content:sync-production --staging` run on staging, after publication. Newsletter delivery is a separate manual step in the admin.

## 7. Editing a live post

Open `/admin/posts/{id}/edit`, put the new text in the editor, and set `data.content` with the snippet above before saving. The "Saved" toast can appear while the content is unchanged. Always confirm on the live page with a cache-busting query string (the page is not CDN-cached, but the check proves the stored content, not the toast). If the browser pane is on that edit page, never reload it while the user is mid-edit.
