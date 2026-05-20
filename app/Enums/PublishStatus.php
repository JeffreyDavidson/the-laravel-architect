<?php

namespace App\Enums;

enum PublishStatus: string
{
    case Draft = 'draft';
    case InReview = 'in_review';
    case Published = 'published';
    case Scheduled = 'scheduled';

    /**
     * @return array<string, string>
     */
    public static function labels(bool $includeInReview = true): array
    {
        $labels = [
            self::Draft->value => 'Draft',
            self::Published->value => 'Published',
            self::Scheduled->value => 'Scheduled',
        ];

        if ($includeInReview) {
            return [
                self::Draft->value => 'Draft',
                self::InReview->value => 'In Review',
                self::Published->value => 'Published',
                self::Scheduled->value => 'Scheduled',
            ];
        }

        return $labels;
    }

    public function color(): string
    {
        return match ($this) {
            self::Published => 'success',
            self::Draft => 'gray',
            self::InReview => 'info',
            self::Scheduled => 'warning',
        };
    }

    public function label(): string
    {
        return self::labels()[$this->value];
    }
}
