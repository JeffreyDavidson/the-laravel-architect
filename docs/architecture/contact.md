# Contact

The contact form saves an inquiry and queues one job that emails the owner and
the sender. The design aims for enqueue atomicity plus per-email idempotency,
not a guarantee of exactly-once delivery by an external mail provider. The
queue worker requirements are in
[Deploying](../operations/deploying.md#queue-worker).

Since 2026-10-09 the contact rules come from `jeffreydavidson/creator-kit`:
`SendContactMessage`, `RetryContactInquiryEmails`,
`ContactInquiryEmailsCannotBeRetried`, the `SendContactInquiryEmails` job, the
`ContactInquiryStatus` enum, the `IsContactInquiry` model concern (encryption,
retry window, pruning). TLA keeps `ContactInquiry`, `StoreContactRequest`, its
`ChecksForSpam` concern (honeypot and Turnstile), the controller,
the form and its mailables; `App\Services\ContactEmails` hands the mailables to
the package, and `config/creator-kit.php` (`contact`) names the model, the notify
address and the retention days. `App\Jobs\SendContactInquiryEmails` only forwards
jobs queued before the move to the package job. The Contact Inquiries admin
screens also come from creator-kit (`CreatorKitPlugin::contactInquiries()` in
`AdminPanelProvider`, with TLA's budget and project title as read-only details);
opening a new inquiry marks it In progress, and the list has Mark resolved and
Reply actions.

## Saving and queueing

Contact inquiries and their one encrypted `SendContactInquiryEmails` job are
inserted in one database transaction.

- The job explicitly uses the `database` queue connection, on the same
  application database connection, with its insert before commit. A separate
  database or non-database queue implementation is rejected before saving.
- Its payload carries only the inquiry ID, and queued contact mail payloads are
  encrypted.
- The worker cannot see uncommitted jobs; a failed enqueue rolls back the
  inquiry too, allowing a clean retry.

## Sending

The job sends the owner notification (`ContactInquiryReceivedMail`) and the sender
confirmation (`ContactConfirmationMail`) separately. It stamps
`notification_sent_at` / `confirmation_sent_at` only after each succeeds, so a
retry sends only what is missing (3 tries, 60/300/900-second backoff, one worker
per inquiry).

Because the sender's address is unverified, the confirmation has a fixed subject
and body and never echoes the inquiry's name, message, or project, so the form
cannot relay visitor text to an arbitrary address. The owner notification
carries the full details as raw plain text.

Each email carries a stable `Resend-Idempotency-Key`. Resend keeps these for 24
hours, so inquiries older than 23 hours fail for manual review instead of
resending. The inquiry's admin page shows both send times and offers **Retry
unsent emails** within that window. The button calls the
`RetryContactInquiryEmails` action, which queues the job again only after an
incomplete delivery attempt inside the window and otherwise throws
`ContactInquiryEmailsCannotBeRetried`, so `SendContactMessage` and this action
are the only places the job is dispatched.

## Abuse controls

The `contact-form` limiter allows three sent messages an hour per IP address,
counting only successful sends. It also counts every attempt against ten a
minute per IP address, so junk submissions cannot trigger unlimited blocking
Turnstile verification calls.

`StoreContactRequest` owns the bot checks. A filled hidden `website` field skips
validation and returns the normal success message without saving anything.
Otherwise the `PassesTurnstile` rule runs only after every other
field is valid; a failure returns to the form with the input except the spent
token. The form's Turnstile loader is an Alpine component (see
[Frontend](frontend.md#alpine-components)).

Contact inquiries cannot be created from the admin (see
[Admin panel](admin-panel.md)).
