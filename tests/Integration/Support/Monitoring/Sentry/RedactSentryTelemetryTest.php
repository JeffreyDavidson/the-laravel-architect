<?php

use App\Support\Monitoring\Sentry\RedactSentryBreadcrumb;
use App\Support\Monitoring\Sentry\RedactSentryEvent;
use App\Support\Monitoring\Sentry\SanitizeSentryData;
use Sentry\Breadcrumb;
use Sentry\ClientBuilder;
use Sentry\Event;
use Sentry\ExceptionDataBag;
use Sentry\Frame;
use Sentry\Serializer\EnvelopItems\EventItem;
use Sentry\Stacktrace;

it('redacts sensitive request and breadcrumb data while retaining exception diagnosis', function () {
    $event = Event::createEvent()
        ->setRequest([
            'url' => 'https://thelaravelarchitect.com/newsletter/confirm/42/confirmation-token-012345678901234567890123456789',
            'query_string' => 'token=confirmation-token&email=reader@example.com',
            'data' => ['email' => 'reader@example.com', 'message' => 'Private project brief from the contact form'],
        ])
        ->setExtra(['api_key' => 'youtube-secret-key'])
        ->setMessage('Unable to process reader@example.com')
        ->setBreadcrumb([
            new Breadcrumb(
                Breadcrumb::LEVEL_INFO,
                Breadcrumb::TYPE_DEFAULT,
                'newsletter',
                'Submitted email reader@example.com',
                ['data' => ['email' => 'reader@example.com']],
            ),
        ])
        ->setExceptions([
            tap(new ExceptionDataBag(new RuntimeException('Unable to subscribe reader@example.com')), function (ExceptionDataBag $exception): void {
                $exception->setStacktrace(new Stacktrace([
                    new Frame('handle', '/app/Actions/RequestNewsletterSubscription.php', 38),
                ]));
            }),
        ]);

    (new RedactSentryEvent(app(SanitizeSentryData::class)))($event);

    $payload = EventItem::toEnvelopeItem($event);

    expect($payload)
        ->not->toContain('confirmation-token', 'reader@example.com', 'youtube-secret-key', 'token=', 'Private project brief')
        ->toContain('RequestNewsletterSubscription.php', '"lineno":38')
        ->and($event->getRequest()['url'])
        ->toBe('https://thelaravelarchitect.com/newsletter/confirm/[Filtered]/[Filtered]')
        ->and($event->getRequest()['query_string'])
        ->toBe('[Filtered]')
        ->and($event->getRequest()['data'])
        ->toBe('[Filtered]')
        ->and($event->getBreadcrumbs()[0]->getMessage())
        ->toBe('Submitted email [Filtered]')
        ->and($event->getExceptions()[0]->getValue())
        ->toBe('Unable to subscribe [Filtered]');
});

it('sanitizes sensitive breadcrumb metadata and outgoing URLs', function () {
    $breadcrumb = new Breadcrumb(
        Breadcrumb::LEVEL_INFO,
        Breadcrumb::TYPE_HTTP,
        'http',
        'GET https://www.googleapis.com/youtube/v3/channels?part=statistics&key=youtube-secret-key',
        [
            'url' => 'https://www.googleapis.com/youtube/v3/channels?part=statistics&key=youtube-secret-key',
            'authorization' => 'Bearer private-token',
        ],
    );

    $redacted = app(RedactSentryBreadcrumb::class)($breadcrumb);

    expect($redacted->getMessage())
        ->toBe('GET https://www.googleapis.com/youtube/v3/channels')
        ->not->toContain('youtube-secret-key')
        ->and($redacted->getMetadata())
        ->toBe([
            'url' => 'https://www.googleapis.com/youtube/v3/channels',
            'authorization' => '[Filtered]',
        ]);
});

it('registers injectable redaction callbacks with the Sentry client options', function () {
    $options = app(ClientBuilder::class)->getOptions();

    expect($options->getBeforeSendCallback())
        ->toBeInstanceOf(RedactSentryEvent::class)
        ->and($options->getBeforeBreadcrumbCallback())
        ->toBeInstanceOf(RedactSentryBreadcrumb::class);
});
