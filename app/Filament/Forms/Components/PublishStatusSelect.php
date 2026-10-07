<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components;

use App\Enums\PublishStatus;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Model;

/**
 * Status field for publishable content. Only pre-publication statuses can be chosen here;
 * content goes live or back to draft through the Publish / Unpublish actions, so a live or
 * scheduled status is shown locked.
 */
final class PublishStatusSelect extends Select
{
    /** @var list<PublishStatus> */
    protected array $selectableStatuses = [PublishStatus::Draft, PublishStatus::InReview];

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->options(fn (?Model $record): array => $this->statusOptions($record))
            ->default(PublishStatus::Draft)
            ->required()
            ->disabled(fn (?Model $record): bool => $this->lockedStatus($record) instanceof PublishStatus)
            ->helperText(fn (?Model $record): ?string => $this->lockedStatus($record) instanceof PublishStatus
                ? 'Use Publish or Unpublish to change a live or scheduled status.'
                : null);
    }

    /** Offer Draft only, for content types without an editorial review step. */
    public function withoutReview(): static
    {
        $this->selectableStatuses = [PublishStatus::Draft];

        return $this;
    }

    /**
     * @return array<string, string>
     */
    private function statusOptions(?Model $record): array
    {
        $statuses = $this->lockedStatus($record) instanceof PublishStatus
            ? [$this->lockedStatus($record)]
            : $this->selectableStatuses;

        return collect($statuses)
            ->mapWithKeys(fn (PublishStatus $status): array => [$status->value => $status->label()])
            ->all();
    }

    private function lockedStatus(?Model $record): ?PublishStatus
    {
        $status = $record?->getAttribute('status');

        return in_array($status, [PublishStatus::Published, PublishStatus::Scheduled], true)
            ? $status
            : null;
    }
}
