<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum NewsletterTemplate: string implements HasLabel
{
    case NewArticle = 'new_article';
    case PracticalTip = 'practical_tip';
    case RelatedReading = 'related_reading';

    public function getLabel(): string
    {
        return match ($this) {
            self::NewArticle => 'New article',
            self::PracticalTip => 'Practical tip',
            self::RelatedReading => 'Related reading',
        };
    }
}
