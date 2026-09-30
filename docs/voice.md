# Voice Guide: The Laravel Architect

How we write for this site: blog posts, excerpts, episode notes, newsletters, and site copy. Anyone drafting public copy, human or agent, follows this file. It is built from the five posts live on thelaravelarchitect.com as of 2026-09-30; update it when the writing evolves.

## Who is talking

- Jeffrey, in first person. A working Laravel developer since 4.2 (2014), writing PHP since 2008, who has spent much of his career modernizing legacy apps (CodeIgniter, ExpressionEngine, Yii2, CakePHP) into current Laravel.
- A practitioner, not a pundit. The site's own line: the best teachers are still practitioners. Advice comes from something he built, shipped, or broke.
- Writing to a fellow developer across the table, usually a working Laravel dev. Never talking down, never selling.

## Voice

- **Direct.** Say the opinion in the first sentence of a section, then explain why. "My controllers are thin. Intentionally, almost aggressively thin."
- **Opinionated, not absolute.** Strong defaults with a stated escape hatch. "Your mileage may vary, and that's fine." Name when a rule doesn't apply.
- **Experience over theory.** Prefer "I switched and lived with the decision" to "best practice says."
- **Honest about limits.** Admit uncertainty and changed minds. "The distinction is admittedly fuzzy, and I don't lose sleep over it."
- **Midwestern plain.** Clear over clever. Kansas "just get it done" sensibility; build what the next developer can understand.
- **Warm and a little self-deprecating.** Light humor at his own expense ("code that would make current-me break out in hives"), never at the reader's.

## Rhythm and structure

- Mix longer explanatory sentences with short punches: "That's it." "Period." "Use them." Use the punch to land a point, not every paragraph.
- Short paragraphs: two to five sentences.
- H2 headings in Title Case, short and concrete, sometimes with a twist: "Controllers: Thin and Boring", "Flat Land, Big Sky", "Enums: Because Magic Strings Are Evil".
- Open with the problem or a relatable scene, not a definition. Explain the problem before listing features or packages.
- Include a "what I don't do" or tradeoffs section in opinion and architecture posts.
- Close by inviting conversation ("The whole point of writing this stuff down is to start conversations, not end them") or with a practical next step. "Rock Chalk" is a sign-off for personal posts only.
- Excerpts: one or two sentences in first person that state the post's promise. No clickbait.
- Length: most posts land between 1,100 and 1,700 words.

## Code examples

- Real, runnable, current-version code. Prefer examples from projects he actually runs, including this site.
- Show the code, then explain the decision behind it in a short paragraph.
- Tests use Pest. Follow the PHP and Laravel conventions in the repo's instructions (enums over magic strings, early returns, FormRequests, and so on); the blog should practice what it preaches.
- Keep examples small enough to read without scrolling sideways. Trim unrelated lines.

## Word choices

**Never use:**
- Em dashes. Use a period, comma, colon, or parentheses.
- Generic AI phrasing: "in today's fast-paced world", "let's dive in", "delve", "game-changer", "unlock", "supercharge", "elevate", "seamless", "robust", "leverage" (as a verb), "it's worth noting", "in conclusion", "the landscape", "navigate the complexities".
- Filler openers: "In this post, we'll explore...". Start with the point.
- Hype about tools. Describe what they do and where they fall short.

**Watch (overused in current posts):**
- "genuinely" (about a dozen uses across five posts) and "honestly". Keep at most one per post.
- "Here's..." as a sentence opener. Fine occasionally; vary it.

**Prefer:**
- Contractions (I'm, it's, don't).
- Plain verbs: use, build, ship, break, fix.
- Concrete nouns: "a Form Request", "a queued job", not "a solution".

## Honesty rules

- Never invent anecdotes, roles, clients, metrics, quotes, or production outcomes. If a story would help and none is on record, leave `[CONFIRM: ...]` for Jeffrey to fill in or cut.
- Exception: a composite example is fine when it describes a recurring pattern rather than one event. Frame it as something that keeps happening ("It usually shows up on something small..."), keep it plausible for any working Laravel developer, and never attach a named project, client, date, or specific result. Rough estimates like "twenty minutes" are fine; measured metrics are not.
- Label work in progress as in progress.
- Recheck package versions, APIs, prices, and security claims against official sources right before publishing. For posts that depend on a source, fill in the post's source URL and review date in the admin.
- Link to official docs instead of paraphrasing them at length.

## Personal details

- Safe to reference when relevant (already public on the site): Kansas roots and the Jayhawks, the move to Florida in 2015, Cassie and Viola, Walt Disney World, poker, photography, faith as part of who he is, Full Sail, discovering Laravel 4.2 in 2014.
- Do not add new private details (health, family specifics beyond what's published, finances) without asking.
- Don't list proficiency ratings or skill levels.
- Check the current podcast positioning before mentioning the shows; it has changed since the early posts.
- Reference Adam Wathan's TDD course when testing habits come up.

## Before and after

> **Before:** In today's fast-paced development landscape, leveraging Form Requests can seamlessly elevate your validation strategy.
>
> **After:** Every form submission gets a Form Request. No inline validation in controllers. Ever.

> **Before:** In this post, we'll explore some of the key benefits of using enums in your Laravel applications.
>
> **After:** If a value has a fixed set of options, it's an enum. Period.

> **Before:** This approach is genuinely robust and honestly a game-changer for teams.
>
> **After:** I've used this on enough projects, solo and on teams, to trust it from small apps to medium-large ones.

## Pre-publish checklist

- [ ] Opens with the problem or a scene, not a definition or "In this post".
- [ ] Each section leads with its point.
- [ ] Includes tradeoffs or when not to do this.
- [ ] No em dashes; no phrases from the never-use list; "genuinely" at most once.
- [ ] Every specific anecdote, number, and quote is real; composite examples read as a recurring pattern. No `[CONFIRM]` left.
- [ ] Code runs on current versions and follows the repo conventions.
- [ ] Time-sensitive claims rechecked; source URL and review date set when relevant.
- [ ] Excerpt is one or two first-person sentences.
- [ ] Ends with a conversation invite or a practical next step.
