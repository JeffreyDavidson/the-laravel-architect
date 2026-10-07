<x-mail.layout :title="$issue->title" :preheader="$preheader">
    <h1 class="mail-heading" style="margin: 0 0 24px; font-size: 26px; line-height: 1.25; font-weight: 800; color: #111827;">
        {{ $issue->title }}
    </h1>
    @if ($unsubscribeUrl === null)
        <p class="mail-note" style="margin: 0 0 24px; padding: 12px 14px; background: #eaf3fa; border-left: 3px solid #4a7fbf; border-radius: 4px; font-size: 14px; line-height: 1.6; color: #374151;">
            This is a test email. Subscribers receive their own unsubscribe link in the footer.
        </p>
    @endif
    <div class="mail-content mail-text" style="font-size: 16px; line-height: 1.65; color: #374151;">
        {!! $bodyHtml !!}
    </div>
    <x-slot:footer>
        <p style="margin: 0 0 8px;">
            <x-mail.link :href="$issueUrl">Read this issue on the web</x-mail.link>
        </p>
        <p style="margin: 0 0 8px;">
            You're receiving this because you confirmed a subscription to The Laravel Architect newsletter.
            @if ($unsubscribeUrl !== null)
                <x-mail.link :href="$unsubscribeUrl">Unsubscribe</x-mail.link>
            @endif
        </p>
    </x-slot:footer>
</x-mail.layout>
