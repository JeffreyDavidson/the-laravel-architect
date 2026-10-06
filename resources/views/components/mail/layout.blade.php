@props([
    'title',
    'preheader' => '',
])

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="color-scheme" content="light dark" />
    <meta name="supported-color-schemes" content="light dark" />
    <title>{{ $title }}</title>
    <style>
        .mail-content a {
            color: #3f6fa8;
            text-decoration: underline;
        }
        .mail-content img {
            max-width: 100%;
            height: auto;
        }
        .mail-content pre {
            overflow-x: auto;
            padding: 12px;
            background: #f3f4f6;
            border-radius: 6px;
            font-size: 14px;
        }
        .mail-content code {
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: 0.95em;
        }
        @media (prefers-color-scheme: dark) {
            .mail-content a {
                color: #6fa3d6 !important;
            }
            .mail-content pre {
                background: #1b2430 !important;
            }
            body,
            .mail-page {
                background: #0b1016 !important;
            }
            .mail-card {
                background: #12171e !important;
                border-color: #1e2a3a !important;
            }
            .mail-heading {
                color: #f3f4f6 !important;
            }
            .mail-text {
                color: #d1d5db !important;
            }
            .mail-muted {
                color: #9ca3af !important;
            }
            .mail-note {
                background: #16222f !important;
                color: #d1d5db !important;
                border-color: #4a7fbf !important;
            }
            .mail-rule {
                border-color: #1e2a3a !important;
            }
            .mail-link {
                color: #6fa3d6 !important;
            }
        }
        @media (max-width: 480px) {
            .mail-pad {
                padding-left: 20px !important;
                padding-right: 20px !important;
            }
        }
    </style>
</head>
<body
    style="
        margin: 0;
        padding: 0;
        background: #eef2f7;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif;
    "
>
    @if ($preheader !== '')
        <div
            style="
                display: none;
                max-height: 0;
                overflow: hidden;
                opacity: 0;
                font-size: 1px;
                line-height: 1px;
                color: #eef2f7;
            "
        >
            {{ $preheader }}
        </div>
    @endif
    <table
        role="presentation"
        width="100%"
        cellpadding="0"
        cellspacing="0"
        class="mail-page"
        style="background: #eef2f7"
    >
        <tr>
            <td align="center" style="padding: 32px 16px">
                <!--[if mso]>
                    <table role="presentation" align="center" width="600" cellpadding="0" cellspacing="0">
                        <tr>
                            <td>
                <![endif]-->
                <table
                    role="presentation"
                    width="100%"
                    cellpadding="0"
                    cellspacing="0"
                    class="mail-card"
                    style="
                        max-width: 600px;
                        background: #ffffff;
                        border: 1px solid #dbe3ec;
                        border-radius: 12px;
                        overflow: hidden;
                    "
                >
                    <tr>
                        <td
                            class="mail-pad"
                            style="padding: 20px 32px; background: #0d0f12; border-bottom: 3px solid #4a7fbf"
                        >
                            <table role="presentation" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="padding-right: 14px; vertical-align: middle">
                                        <img
                                            src="{{ url('/images/elephant-companion-180.png') }}"
                                            width="48"
                                            height="48"
                                            alt=""
                                            style="display: block; border: 0; width: 48px; height: 48px"
                                        />
                                    </td>
                                    <td style="vertical-align: middle">
                                        <div
                                            style="
                                                font-family: 'SFMono-Regular', Menlo, Consolas, monospace;
                                                font-size: 11px;
                                                letter-spacing: 0.22em;
                                                text-transform: uppercase;
                                                color: #6fa3d6;
                                            "
                                        >
                                            The Laravel
                                        </div>
                                        <div
                                            style="
                                                font-size: 22px;
                                                font-weight: 800;
                                                letter-spacing: 0.06em;
                                                text-transform: uppercase;
                                                color: #ffffff;
                                            "
                                        >
                                            Architect
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td class="mail-pad" style="padding: 36px 32px 32px">{{ $slot }}</td>
                    </tr>
                    <tr>
                        <td
                            class="mail-pad mail-rule mail-muted"
                            style="
                                padding: 20px 32px 28px;
                                border-top: 1px solid #e5e7eb;
                                font-size: 12px;
                                line-height: 1.6;
                                color: #6b7280;
                            "
                        >
                            {{ $footer ?? '' }}
                            <p style="margin: 0">
                                The Laravel Architect ·
                                <a href="{{ route('home') }}" class="mail-link" style="color: #3f6fa8"
                                    >thelaravelarchitect.com</a>
                                ·
                                <a href="{{ route('privacy') }}" class="mail-link" style="color: #3f6fa8">Privacy</a>
                            </p>
                        </td>
                    </tr>
                </table>
                {{-- Echoed so the Blade formatter cannot move these closing tags out of the Outlook-only comment. --}}
                {!! '<!--[if mso]></td></tr></table><![endif]-->' !!}
            </td>
        </tr>
    </table>
</body>
</html>
