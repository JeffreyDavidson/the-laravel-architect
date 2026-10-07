# Sending a newsletter issue

How sending works under the hood is described in
[Newsletter](../../architecture/newsletter.md#sending-an-issue).

**Send test email** and **Send to subscribers** both save the edit form before
sending, so unsaved edits are stored and the email matches what is on screen. If
the form has invalid data, the validation errors show and nothing is sent.

1. Publish the issue, then use **Send test email** on its edit page. The copy
   goes to `MAIL_CONTACT_TO`, records no delivery, and omits unsubscribe
   headers.
2. Check the Resend dashboard's daily and monthly sending quota against the
   active-subscriber count shown in the send confirmation. A send that exceeds
   the quota fails part-way; remaining deliveries retry for up to a day.
3. Confirm the production database queue worker is running, then use **Send to
   subscribers**. The button appears only for published issues that have not
   been sent, and a send cannot be undone.
4. Watch the edit page subheading (`Delivered to N of M subscribers`).
   Deliveries are rate-limited to five per second. Subscribers who unsubscribe
   before their delivery runs are skipped and removed from the count.
5. Deliveries that still fail after three exceptions or a day of retries appear
   in Nightwatch as failed jobs. Retrying a failed job is safe: a delivery that
   was already sent is skipped.

Staging keeps `MAIL_MAILER=log` and never receives production subscribers, so
sends there cannot reach readers.
