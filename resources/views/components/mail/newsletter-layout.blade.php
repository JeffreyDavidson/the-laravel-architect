@props(['title', 'preheader', 'mastheadUrl'])
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="color-scheme" content="light dark" />
    <meta name="supported-color-schemes" content="light dark" />
    <title>{{ $title }}</title>
    <style>
        body {
            margin: 0;
            background: #f0f3f6;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif;
            color: #1f2328;
        }
        table {
            border-collapse: collapse;
        }
        img {
            border: 0;
            display: block;
        }
        a {
            color: #245b91;
        }
        .newsletter-sheet {
            width: 100%;
            max-width: 640px;
            background: #fff;
        }
        .newsletter-pad {
            padding-left: 44px;
            padding-right: 44px;
        }
        .newsletter-heading {
            font-size: 38px;
            line-height: 1.13;
            letter-spacing: -1px;
            margin: 0 0 18px;
            font-weight: 750;
        }
        .newsletter-muted {
            color: #424a53;
        }
        .newsletter-footer {
            color: #656d76;
            font-size: 12px;
            line-height: 1.7;
        }
        .newsletter-rule {
            border-top: 1px solid #d0d7de;
        }
        .newsletter-button {
            display: inline-block;
            background: #3f6fa8;
            color: #fff !important;
            padding: 15px 28px;
            font-size: 16px;
            line-height: 22px;
            font-weight: 700;
            text-decoration: none;
            border-radius: 3px;
        }
        @media (max-width: 480px) {
            .newsletter-outer {
                padding: 0 !important;
            }
            .newsletter-pad {
                padding-left: 26px !important;
                padding-right: 26px !important;
            }
            .newsletter-heading {
                font-size: 32px !important;
                letter-spacing: -0.8px !important;
            }
            .newsletter-button {
                display: block !important;
                text-align: center !important;
            }
            .newsletter-metadata {
                font-size: 13px !important;
            }
        }
        @media (prefers-color-scheme: dark) {
            body {
                background: #0d0f12 !important;
                color: #f6f8fa !important;
            }
            .newsletter-sheet {
                background: #0d1117 !important;
            }
            .newsletter-muted {
                color: #c9d1d9 !important;
            }
            .newsletter-content {
                color: #f6f8fa !important;
            }
            .newsletter-topic,
            .newsletter-content a,
            a {
                color: #a8cce8 !important;
            }
            .newsletter-content h2,
            .newsletter-content h3,
            .newsletter-content li {
                border-color: #424a53 !important;
            }
            .newsletter-content blockquote {
                background: #161b22 !important;
            }
            .newsletter-footer {
                color: #a8b1bc !important;
            }
            .newsletter-rule {
                border-color: #424a53 !important;
            }
            .newsletter-button {
                color: #fff !important;
            }
        }
    </style>
</head>
<body
    style="
        margin: 0;
        background: #f0f3f6;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif;
        color: #1f2328;
    "
>
    <div style="display: none; max-height: 0; overflow: hidden; opacity: 0">{{ $preheader }}</div>
    <table role="presentation" width="100%">
        <tr>
            <td class="newsletter-outer" align="center" style="padding: 36px 16px">
                {!! '<!--[if mso]><table role="presentation" width="640"><tr><td><![endif]-->' !!}
                <table
                    role="presentation"
                    class="newsletter-sheet"
                    width="640"
                    style="width: 100%; max-width: 640px; background: #fff"
                >
                    <tr>
                        <td class="newsletter-pad" style="padding: 24px 44px 19px; background: #fff; color: #1f2328">
                            <img
                                src="{{ $mastheadUrl }}"
                                width="339"
                                height="66"
                                alt="The Laravel Architect"
                                style="width: 100%; max-width: 339px; height: auto"
                            />
                            <table
                                role="presentation"
                                width="100%"
                                style="margin-top: 18px; border-top: 1px solid #d0d7de"
                            >
                                <tr>
                                    <td
                                        class="newsletter-metadata"
                                        style="padding-top: 12px; font-size: 14px; line-height: 20px; color: #1f2328"
                                    >
                                        From Jeffrey Davidson
                                    </td>
                                    <td
                                        class="newsletter-metadata"
                                        align="right"
                                        style="padding-top: 12px; font-size: 14px; line-height: 20px; color: #245b91"
                                    >
                                        Newsletter
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    {{ $slot }}
                    <tr>
                        <td
                            class="newsletter-pad newsletter-footer newsletter-rule"
                            style="
                                padding: 24px 44px 28px;
                                border-top: 1px solid #d0d7de;
                                color: #656d76;
                                font-size: 12px;
                                line-height: 1.7;
                            "
                        >
                            {{ $footer }}
                        </td>
                    </tr>
                </table>
                {!! '<!--[if mso]></td></tr></table><![endif]-->' !!}
            </td>
        </tr>
    </table>
</body>
</html>
