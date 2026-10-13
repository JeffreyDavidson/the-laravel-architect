<x-mail.newsletter-layout :title="$issue->title" :preheader="$preheader" :masthead-url="$details['mastheadUrl']">
    <tr><td class="newsletter-pad" style="padding:38px 44px 24px">
        @if ($details['topic'])
            <p class="newsletter-topic" style="font-size:12px;line-height:18px;color:#245b91;margin:0 0 14px">New article · {{ $details['topic'] }}</p>
        @endif
        <h1 class="newsletter-heading" style="font-size:38px;line-height:1.13;letter-spacing:-1px;font-weight:750;margin:0 0 18px">{{ $issue->title }}</h1>
        @if ($details['deck'])
            <p class="newsletter-muted" style="font-size:19px;line-height:1.5;margin:0;color:#424a53">{{ $details['deck'] }}</p>
        @endif
        @if ($unsubscribeUrl === null)
            <p class="newsletter-muted" style="font-size:14px;line-height:1.6;margin:20px 0 0;color:#424a53">This is a test email. Subscribers receive their own unsubscribe link in the footer.</p>
        @endif
    </td></tr>
    @if ($details['heroUrl'])
        <tr><td><img src="{{ $details['heroUrl'] }}" width="640" alt="{{ $details['heroAlt'] }}" style="width:100%;height:auto"></td></tr>
    @endif
    <tr><td class="newsletter-pad" style="padding:24px 44px 20px">{!! $bodyHtml !!}</td></tr>
    @if ($details['articleUrl'])
        <tr><td class="newsletter-pad" style="padding:0 44px 38px"><a class="newsletter-button" href="{{ $details['articleUrl'] }}" style="display:inline-block;background:#3f6fa8;color:#fff;padding:15px 28px;font-size:16px;line-height:22px;font-weight:700;text-decoration:none;border-radius:3px">{{ $details['buttonLabel'] }}</a></td></tr>
    @endif
    <x-slot:footer>
        <p style="margin:0 0 10px">You're receiving this because you confirmed a subscription to The Laravel Architect newsletter.</p>
        <p style="margin:0"><a href="{{ $issueUrl }}">Read on the web</a>
        @if ($unsubscribeUrl !== null)
            · <a href="{{ $unsubscribeUrl }}">Unsubscribe</a>
        @endif
        · <a href="{{ route('privacy') }}">Privacy</a></p>
    </x-slot:footer>
</x-mail.newsletter-layout>
