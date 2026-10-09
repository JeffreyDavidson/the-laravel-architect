---
paths:
  - 'app/Mail/**'
  - 'app/Notifications/**'
---

# Mail

## Name mailables and notifications after their purpose
Mailables live in `App\Mail`, extend `Mailable` and end with `Mail` (`NewsletterConfirmationMail`, `ContactInquiryReceivedMail`), so they never clash with the Action that triggers them (creator-kit's `ConfirmNewsletterSubscription`). creator-kit's newsletter actions and delivery job get TLA's newsletter mailables through `App\Services\NewsletterEmails` (bound to the package's `NewsletterMails`), which lives outside `App\Mail` because every class there is a mailable. Notifications live in `App\Notifications` and end with `Notification` (`BackupDeliveryTestNotification`). `tests/Architecture/MailArchitectureTest.php` enforces both suffixes.

## Send mail through the injected Mailer contract
Application code never uses the `Mail` facade (`MailArchitectureTest` forbids it). Inject `Illuminate\Contracts\Mail\Mailer` instead: Actions and services take it in the constructor, because callers invoke `handle()` directly (`RequestNewsletterSubscription`, `SendNewsletterIssueTestEmail`); queued jobs take it as a `handle(Mailer $mailer)` argument, because the container calls `handle()` (`SendContactInquiryEmails`, `DeliverNewsletterIssue`). Tests still use `Mail::fake()`, which swaps the mail manager the contract resolves from.

## Fingerprint idempotency keys once
A mailable whose send is retried carries a `Resend-Idempotency-Key` built from creator-kit's `IdempotencyFingerprint::for()` (the app URL, the record's ID and its creation time), with a fixed per-email prefix or suffix. Do not change the fingerprint's inputs or the key text: Resend keeps keys for 24 hours, and a different key for the same email lets a retry deliver it twice. The mail tests pin each key to a fixed value.

## Keep rendering out of mailables
A mailable assembles its envelope, headers and content. Formatting one record for the email, such as a newsletter issue's Markdown body with absolute URLs and its preheader, belongs to that model's presenter (`NewsletterIssuePresenter::emailBodyHtml()`, `emailBodyText()`, `emailPreheader()`).

## Deploy mailable renames with an empty queue
Queued mailables and notifications serialise their class name into the job payload, so renaming or moving one strands any job already queued. Deploy such a change only after the queue is empty, and say so in the pull request.
