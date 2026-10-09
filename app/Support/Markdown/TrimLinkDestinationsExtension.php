<?php

declare(strict_types=1);

namespace App\Support\Markdown;

use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Event\DocumentParsedEvent;
use League\CommonMark\Extension\CommonMark\Node\Inline\AbstractWebResource;
use League\CommonMark\Extension\ExtensionInterface;

/**
 * CommonMark keeps the spaces in a destination written as `[text](< /path >)` and encodes
 * them, producing `%20/path%20`, which no browser or mail client resolves. This trims
 * whitespace from both ends of every link and image destination after parsing; spaces
 * inside a destination (`</my post>`) are kept, and code is never touched.
 */
final class TrimLinkDestinationsExtension implements ExtensionInterface
{
    public function register(EnvironmentBuilderInterface $environment): void
    {
        $environment->addEventListener(DocumentParsedEvent::class, $this->trimDestinations(...));
    }

    private function trimDestinations(DocumentParsedEvent $event): void
    {
        $nodes = $event
            ->getDocument()
            ->iterator();

        foreach ($nodes as $node) {
            if ($node instanceof AbstractWebResource) {
                $url = $node->getUrl();
                $node->setUrl(preg_replace('/^(?:%20|\s)+|(?:%20|\s)+$/', '', $url) ?? $url);
            }
        }
    }
}
