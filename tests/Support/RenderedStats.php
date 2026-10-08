<?php

namespace Tests\Support;

use Dom\HTMLDocument;

/**
 * Reads the stats a rendered Filament stats overview widget shows, so widget tests can
 * assert the visible values and links instead of calling the widget's protected methods.
 */
class RenderedStats
{
    /**
     * Each stat's value and link, keyed by its label, in display order.
     *
     * @return array<string, array{value: string, url: string|null}>
     */
    public static function from(string $html): array
    {
        $document = HTMLDocument::createFromString('<!DOCTYPE html><html><body>'.$html.'</body></html>');
        $stats = [];

        foreach ($document->querySelectorAll('.fi-wi-stats-overview-stat') as $stat) {
            $label = $stat->querySelector('.fi-wi-stats-overview-stat-label');
            $value = $stat->querySelector('.fi-wi-stats-overview-stat-value');

            $stats[trim($label->textContent ?? '')] = [
                'value' => trim($value->textContent ?? ''),
                'url' => $stat->getAttribute('href'),
            ];
        }

        return $stats;
    }
}
