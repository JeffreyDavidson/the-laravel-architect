# Moving podcast episodes to Transistor

TLA's shows are not on Transistor yet. Public episode playback already uses only
a Transistor player (or YouTube); the old audio players and embeds are retired
(see [Publishing and content](../../architecture/publishing-and-content.md#podcasts-and-episodes)).
When a show moves:

1. For each episode, copy its share link from Transistor
   (`https://share.transistor.fm/s/{id}`) into **Transistor episode URL** on the
   episode's edit page. The form rejects any other URL.
2. Use the episodes table's **Missing Transistor URL** filter as the checklist:
   it lists published episodes that still have no share link. The hidden
   **Transistor** column shows which episodes are done.
3. Check a few public episode pages: once a share link is set, the page shows
   the Transistor player.
4. Audio files uploaded before the retirement are no longer referenced by any
   record. Any left under `storage/app/public/episodes/audio/` appear as orphans
   in the media orphan report and can be reviewed and removed with it (see
   [Media](../media.md#reviewing-orphaned-media)).
