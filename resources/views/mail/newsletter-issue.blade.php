<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>{{ $issue->title }}</title>
        <style>
            .newsletter-body a { color: #2b3a4e; text-decoration: underline; }
            .newsletter-body img { max-width: 100%; height: auto; }
            .newsletter-body pre { overflow-x: auto; padding: 12px; background: #f3f4f6; border-radius: 6px; font-size: 14px; }
            .newsletter-body code { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: 0.95em; }
        </style>
    </head>
    <body style="margin: 0; padding: 0; background: #f9fafb; color: #111827; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif;">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background: #f9fafb;">
            <tr>
                <td align="center" style="padding: 32px 16px;">
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; background: #ffffff; border-radius: 8px;">
                        <tr>
                            <td style="padding: 32px;">
                                <p style="margin: 0 0 8px; font-size: 13px; letter-spacing: 0.08em; text-transform: uppercase; color: #6b7280;">
                                    The Laravel Architect
                                </p>
                                <h1 style="margin: 0 0 24px; font-size: 26px; line-height: 1.25; color: #111827;">
                                    {{ $issue->title }}
                                </h1>
                                @if ($unsubscribeUrl === null)
                                    <p style="margin: 0 0 24px; padding: 12px; background: #fef3c7; border-radius: 6px; font-size: 14px; color: #92400e;">
                                        This is a test email. Subscribers receive their own unsubscribe link in the footer.
                                    </p>
                                @endif
                                <div class="newsletter-body" style="font-size: 16px; line-height: 1.65;">
                                    {!! $bodyHtml !!}
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 24px 32px; border-top: 1px solid #e5e7eb; font-size: 13px; line-height: 1.6; color: #6b7280;">
                                <p style="margin: 0 0 8px;">
                                    <a href="{{ $issueUrl }}" style="color: #2b3a4e;">Read this issue on the web</a>
                                </p>
                                <p style="margin: 0;">
                                    You're receiving this because you confirmed a subscription to The Laravel Architect newsletter.
                                    @if ($unsubscribeUrl !== null)
                                        <a href="{{ $unsubscribeUrl }}" style="color: #2b3a4e;">Unsubscribe</a>
                                    @endif
                                </p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
</html>
