# Newsletter

The newsletter has three parts: a double-opt-in subscription, issues published
on the site, and emailed deliveries of those issues. Bounces and complaints come
back through a Resend webhook. The operator steps are in the
[send runbook](../operations/runbooks/newsletter-send.md) and the
[webhook runbook](../operations/runbooks/resend-webhook.md).

## Signing up

The signup block has the anchor `newsletter-form`. With JavaScript, it is an
Alpine component (`newsletterForm`, registered from
`resources/js/pages/newsletter.js` and loaded only on pages that have the form)
and submits in place:

- it posts with `Accept: application/json`;
- `NewsletterSubscriptionController::store` answers `{message}` (or a 422 with
  the validation error, or 429 from the rate limit);
- the component shows the matching banner in the block's live region, clearing
  the field on success and keeping it on error;
- the server-rendered banners (from a redirect with a flashed message) are
  hidden once the component has a message of its own.

Without JavaScript the same form posts normally and redirects back. Every
redirect that shows its message or error (subscribe, rejected sign-up,
confirmation and unsubscribe) ends on the `newsletter-form` fragment, so the
visitor lands on the message and not at the top of the page. A successful
confirmation is the exception: it ends on the confirmed page instead.

The `newsletter` limiter allows five sign-ups an hour per IP address.

## Confirmation email

The newsletter confirmation email (`NewsletterConfirmationMail`, queued and
encrypted) is multipart:

- a themed HTML version (`mail/newsletter-confirmation`, built on the shared
  `x-mail.layout` component with inline styles, the site's brand colors, the
  small elephant logo, a button and a copy-and-paste fallback link, and a
  dark-mode variant);
- a plain-text version (`mail/newsletter-confirmation-text`), in which the
  confirmation URL is rendered without HTML escaping.

The signed link expires after one day.

Confirmation emails have a 15-minute cooldown per normalized email address,
coordinated through hashed cache keys and an atomic lock. Repeated requests
during that window preserve the existing confirmation link, including requests
from different IP addresses. An enqueue failure leaves retries available; the
public response does not disclose subscription status.

## Confirming

Newsletter subscriptions use a signed, expiring double-opt-in link whose page
confirms only through a POST of its confirmation form, so link scanners that
just fetch the page change nothing.

- Confirmation routes check the URL signature and compare the presented token
  against a non-mass-assignable SHA-256 hash in
  `EnsureValidNewsletterConfirmationLink`.
- The page is rendered in its "Confirming your subscription…" state from the
  first paint. In a browser the `newsletterConfirm` Alpine component
  (`resources/js/pages/newsletter-confirm.js`) submits that form as soon as the
  page starts, so the email's button confirms in one click and the text never
  changes before the confirmed page loads.
- The form's button sits in a `<noscript>` block for visitors without
  JavaScript. With JavaScript, the component reveals a hidden copy of it only if
  submitting throws or the page is still showing after five seconds.
- The confirmation page is sent with `Cache-Control: no-store, private`, so it
  and its CSRF token and email are never cached and the back button reloads it.
  If a browser still restores it from the back-forward cache, the component
  shows the button instead of the spinner.
- Only the confirmation page carries the `data-newsletter-confirm` hook, so the
  unsubscribe page never submits by itself.
- A scanner that runs JavaScript could still confirm, as on most
  double-opt-in sites.
- Confirmation links share the `newsletter-confirm` rate limiter (10 requests a
  minute per IP address).

A successful confirmation redirects to the `/newsletter/confirmed` page
(`newsletter.confirmed`, `NewsletterConfirmedController`,
`NewsletterConfirmedViewModel`), which needs no token, shows no subscriber data,
lists the three newest published posts, and is not indexed.

An expired, altered, already used or otherwise unusable confirmation link,
including one whose subscriber has been pruned, confirms nothing and redirects
to the home page's signup form with one generic error in the existing banner, so
the response does not reveal whether the subscriber exists.

## Unsubscribing

Subscriber-specific signed unsubscribe links use the same explicit form pattern
and are included in every newsletter. `SubscriberPresenter::unsubscribeUrl()`
builds them, and `SubscriberPresenter::confirmationUrl()` builds the signed
confirmation link.

- Unsubscribe links are permanent signed URLs, because a newsletter can be read
  long after it is sent.
- The unsubscribe page also shows the email and a CSRF token, so it is sent with
  `Cache-Control: no-store, private` too.
- Each email carries `List-Unsubscribe` and `List-Unsubscribe-Post` headers. Mail
  providers' RFC 8058 one-click requests post to the same signed URL, which is
  exempt from request-forgery tokens because the signature authorizes it.
- The unsubscribe page, its form and the one-click POST use the
  `newsletter-unsubscribe` limiter (120 a minute per IP address), separate from
  confirmation, because mail providers send one-click unsubscribes from a few
  shared addresses, often in a burst after a send, and the signed link already
  authorizes them.
- An altered unsubscribe link gets the standalone branded 403 page (see
  [Security and HTTP](security-and-http.md#error-pages)).

## Subscriber lifecycle

Only verified, non-unsubscribed subscribers count as an active audience.

The daily `model:prune` run deletes subscribers who never confirmed within 7
days of their latest request and unsubscribed subscribers 30 days after
unsubscribing. Resubscribing always requires confirmation again.

The Subscribers admin list shows each subscriber's status (`SubscriberStatus`)
and filters by it, defaulting to active. `Subscriber::status()` derives the
badge and the `withStatus()` scope applies the filter with the same precedence
(suppressed, then unsubscribed, then pending, then active), so a subscriber only
ever appears under the filter that matches its badge. Subscribers cannot be created from the
admin and have no edit page (see [Admin panel](admin-panel.md)).

## Suppression and the Resend webhook

A subscriber whose address bounced or was reported as spam is suppressed
(`suppressed_at` and `suppression_reason`, set by `SuppressSubscriber`). It is
never active, never pruned, never re-sent a confirmation, and never queued for a
newsletter, so a small do-not-email list of those addresses is kept. Suppressed
subscribers cannot be selected for bulk delete, so the do-not-email list cannot
be erased from the admin.

A signed Resend webhook (`POST /webhooks/resend`, `ResendWebhookController` and
`HandleResendWebhook`, outside the web middleware group and rate limited)
suppresses the recipients of permanent bounces, spam complaints and Resend's own
suppressions. Its setup and protections are in the
[webhook runbook](../operations/runbooks/resend-webhook.md).

## Sending an issue

Published newsletter issues are emailed from their Filament edit page. **Send
test email** and **Send to subscribers** save the form first, so unsaved edits
are stored and the email matches the screen; invalid form data stops the action
before anything is sent. A test email to the site owner records no delivery and
omits unsubscribe headers.

`SendNewsletterIssue` refuses unpublished or already-sent issues by throwing
`App\Exceptions\NewsletterIssueCannotBeSent`. Otherwise, in
one transaction, it creates one `newsletter_deliveries` row per active
subscriber, marks the issue sent, and enqueues one `DeliverNewsletterIssue` job
per delivery on the application database queue, so a failure leaves nothing
half-sent.

- With no active subscribers nothing is queued and the issue is not marked
  sent; the edit page says so and the issue can be sent later.
- The unique issue-and-subscriber pair rejects a concurrent second send.

Each job:

- skips deliveries that were already sent;
- drops deliveries for subscribers who are no longer active or for an issue that
  is no longer published (unpublished, trashed or moved to a future date);
- sends `NewsletterIssueMail` immediately and records `sent_at`;
- passes through the `newsletter-delivery` rate limiter (five per second, half
  of Resend's default team limit) and retries for up to a day.

Each subscriber's email carries a `Resend-Idempotency-Key` derived from its
delivery, and Resend keeps keys for 24 hours, so a retry after a send whose
`sent_at` update failed is not delivered twice. The job's 60-second timeout
stays below the database queue's 90-second `retry_after`, so a slow send is not
picked up by a second worker.

Queued newsletter mail payloads are encrypted. Deliveries store no email address
and are deleted with their subscriber or issue.

## The issue email

`NewsletterIssueMail` renders the issue Markdown with the public site's safety
settings as HTML and includes the raw Markdown as plain text; both bodies and the
preheader come from `NewsletterIssuePresenter`. It is built on the shared
themed `x-mail.layout`: dark-mode styles, a preheader from the issue excerpt or
title, an Outlook-only 600px table wrapper, and the unsubscribe link in the
layout footer. Relative link and image URLs in both parts are made absolute with
`config('app.url')` so they work in mail clients.
