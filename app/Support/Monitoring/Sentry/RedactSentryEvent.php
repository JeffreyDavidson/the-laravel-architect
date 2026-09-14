<?php

namespace App\Support\Monitoring\Sentry;

use Sentry\Event;
use Sentry\EventHint;

final readonly class RedactSentryEvent
{
    public function __construct(private SanitizeSentryData $sanitizer) {}

    public function __invoke(Event $event, ?EventHint $hint = null): Event
    {
        $event->setRequest($this->sanitizer->array($event->getRequest()));
        $event->setExtra($this->sanitizer->array($event->getExtra()));

        foreach ($event->getContexts() as $name => $context) {
            $event->setContext($name, $this->sanitizer->array($context));
        }

        $event->setBreadcrumb(array_map(
            $this->sanitizer->breadcrumb(...),
            $event->getBreadcrumbs(),
        ));

        foreach ($event->getExceptions() as $exception) {
            $exception->setValue($this->sanitizer->string($exception->getValue()));
        }

        if ($event->getMessage() !== null) {
            $messageParams = array_map(
                $this->sanitizer->string(...),
                $event->getMessageParams(),
            );

            $event->setMessage(
                $this->sanitizer->string($event->getMessage()),
                $messageParams,
                $event->getMessageFormatted() !== null
                    ? $this->sanitizer->string($event->getMessageFormatted())
                    : null,
            );
        }

        return $event;
    }
}
