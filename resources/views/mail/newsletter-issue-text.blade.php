{!! $issue->title !!}
@if ($unsubscribeUrl === null)

This is a test email. Subscribers receive their own unsubscribe link in the footer.
@endif

{!! $bodyText !!}
@if ($issue->email_template !== null)

Jeffrey
Jeffrey Davidson
Senior Software Engineer
The Laravel Architect
{!! url('/') !!}
@endif

---

Read this issue on the web:
{!! $issueUrl !!}
@if ($unsubscribeUrl !== null)

Unsubscribe:
{!! $unsubscribeUrl !!}
@endif
