# Resend bounce and complaint webhook

Resend reports bounces and spam complaints to
`POST https://thelaravelarchitect.com/webhooks/resend`. The application then
suppresses those addresses so they are never mailed again (see
[Newsletter](../../architecture/newsletter.md#suppression-and-the-resend-webhook)).

## Set it up on production

1. Sign in to the Resend account that owns the `thelaravelarchitect.com` domain,
   not another project's account, and confirm the account name at the top left
   of the dashboard before changing anything.
   - Resend only shows a webhook to the account that owns it, so a webhook
     created in the wrong account never receives this site's events and its
     direct link returns a 404.
   - Resend signs in with Google, so the Google account that is active in the
     browser decides which Resend account opens; choose it at the Google account
     chooser.
   - In 1Password this login is the item named "Resend - The Laravel Architect":
     a Google sign-in for the Google account that owns this site's Resend
     account, which signs in with a passkey. It is separate from the Mouse28 and
     KneadIt Resend login, which owns a different set of domains.
2. Open Webhooks, add `https://thelaravelarchitect.com/webhooks/resend` and
   select `email.bounced`, `email.complained` and `email.suppressed`. Resend
   webhooks are account-wide, so events for other projects' mail on the same
   account also arrive; they are ignored unless the address is one of this
   site's subscribers.
3. Copy the signing secret (`whsec_...`) into the production environment as
   `RESEND_WEBHOOK_SECRET` in Forge, then **deploy** (or run `config:clear` and
   `config:cache`) and restart the queue workers. Production caches config, so a
   saved `.env` value is not used until then.
4. Send a test event from the Resend dashboard and confirm a 2xx in its delivery
   log. A 503 means the secret is not loaded; a 403 means the secret does not
   match this webhook, or Cloudflare blocked the request. The site is behind
   Cloudflare: if the delivery log shows a 403 or a challenge page instead of a
   2xx, add a Cloudflare WAF skip rule for `/webhooks/resend`.
5. Verify the full path with a real bounce: subscribe `bounced@resend.dev` on
   the live site, then confirm the event succeeds in the delivery log and the
   subscriber has `suppressed_at` set with `suppression_reason` of `bounced`.
   Use `complained@resend.dev` the same way for the complaint path, expecting
   `complained`. Only events sent after the webhook exists are delivered; Resend
   does not replay earlier ones.

## Troubleshooting a 403

An empty-body 403 with a secret that looks correct almost always means the
running app still holds the old secret (stale config cache): redeploy, then use
**Replay** on the failed event, or let Resend's automatic retry succeed. To
compare without revealing the secret, run
`php artisan tinker --execute 'echo strlen((string) config("services.resend.webhook_secret"));'`
in Forge and compare the length with the secret shown in Resend.

## How the endpoint is protected

- The endpoint skips the session and CSRF middleware because Resend posts
  server to server; the signature is the only credential.
- It is limited to 60 requests per minute per IP.
- Event payloads and addresses are never logged.
- A malformed signature header gets the same 403 as a wrong one.
- The Resend package's own `/resend/webhook` route is disabled in
  `config/resend.php`.
- `app:verify-deployment` does not require the secret, so a release can ship
  before the webhook exists.
- Staging never sends email and needs no webhook.
