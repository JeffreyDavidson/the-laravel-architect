<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="robots" content="noindex, nofollow" />
    <meta name="color-scheme" content="light dark" />
    <title>
        @yield('code')
        ·
        @yield('title')
        — The Laravel Architect
    </title>
    <link rel="icon" type="image/png" href="/images/elephant-companion-32.png" />
    {{-- Keep recovery styling inline: a missing Vite manifest must not cause another server error. --}}
    <style>
        :root {
            color-scheme: light dark;
            --page: #ffffff;
            --text: #1f2328;
            --muted: #424a53;
            --line: #d0d7de;
            --accent: #245b91;
            --action: #3f6fa8;
            --action-hover: #345f91;
        }
        @media (prefers-color-scheme: dark) {
            :root {
                --page: #0d1117;
                --text: #ffffff;
                --muted: #c9d1d9;
                --line: #303d4d;
                --accent: #7eb0dc;
            }
        }
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            background: var(--page);
            color: var(--text);
            font-family: 'IBM Plex Sans', ui-sans-serif, system-ui, sans-serif;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }
        ::selection {
            background: var(--action);
            color: #ffffff;
        }
        a {
            color: inherit;
            text-underline-offset: 0.25em;
        }
        a:focus-visible {
            outline: 3px solid var(--accent);
            outline-offset: 5px;
        }
        .shell {
            width: min(100% - 3rem, 76rem);
            margin-inline: auto;
        }
        header {
            padding-block: 1.5rem;
            border-bottom: 1px solid var(--line);
        }
        .brand {
            display: inline-flex;
            align-items: center;
            gap: 0.875rem;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
        }
        .brand img {
            width: 40px;
            height: 40px;
            object-fit: contain;
        }
        main {
            display: flex;
            align-items: center;
            min-height: 70vh;
            padding-block: 4rem;
        }
        .error {
            display: grid;
            gap: 1.5rem;
            width: 100%;
        }
        .code {
            margin: 0;
            color: var(--accent);
            font-family: ui-monospace, monospace;
            font-size: 0.875rem;
        }
        h1 {
            margin: 0;
            max-width: 14ch;
            font-size: clamp(2.5rem, 6vw, 4.5rem);
            line-height: 1.1;
            letter-spacing: -0.025em;
            text-wrap: balance;
        }
        .message {
            max-width: 36rem;
            margin: 1.5rem 0 0;
            color: var(--muted);
            font-size: 1.125rem;
        }
        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-top: 2.5rem;
        }
        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 48px;
            padding: 0.75rem 1.5rem;
            border: 1px solid var(--line);
            border-radius: 0.5rem;
            font-weight: 600;
            text-decoration: none;
        }
        .button:hover {
            border-color: var(--accent);
        }
        .primary {
            border-color: var(--action);
            background: var(--action);
            color: #ffffff;
        }
        .primary:hover {
            border-color: var(--action-hover);
            background: var(--action-hover);
        }
        .note {
            max-width: 36rem;
            margin: 2rem 0 0;
            color: var(--muted);
            font-size: 0.875rem;
        }
        footer {
            padding-block: 1.5rem;
            border-top: 1px solid var(--line);
            color: var(--muted);
            font-size: 0.875rem;
        }
        @media (min-width: 768px) {
            .error {
                grid-template-columns: 10rem minmax(0, 1fr);
                gap: 3.5rem;
            }
            .code {
                padding-top: 0.75rem;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="shell">
            <a class="brand" href="/">
                <img src="/images/elephant-companion-128.webp" width="40" height="40" alt="" />
                The Laravel Architect
            </a>
        </div>
    </header>
    <main class="shell">
        <div class="error">
            <p class="code">
                Error /
                @yield('code')
            </p>
            <div>
                <h1>@yield('title')</h1>
                <p class="message">@yield('message')</p>
                <div class="actions">
                    {{-- An ordinary link retries with GET, never resubmitting a failed POST. --}}
                    <a class="button primary" href="">Try again</a>
                    <a class="button" href="/">Go home</a>
                </div>
                @hasSection('note')
                    <p class="note">@yield('note')</p>
                @endif
            </div>
        </div>
    </main>
    <footer class="shell">The Laravel Architect · Jeffrey Davidson</footer>
</body>
</html>
