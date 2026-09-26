{!! $issue->title !!}
@if ($unsubscribeUrl === null)

This is a test email. Subscribers receive their own unsubscribe link in the footer.
@endif

{!! $issue->content !!}

---

Read this issue on the web:
{!! $issueUrl !!}
@if ($unsubscribeUrl !== null)

Unsubscribe:
{!! $unsubscribeUrl !!}
@endif
