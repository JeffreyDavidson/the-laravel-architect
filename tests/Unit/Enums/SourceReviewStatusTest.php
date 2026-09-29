<?php

use App\Enums\SourceReviewStatus;

it('labels and colours each source review status', function (SourceReviewStatus $status, string $label, string $color) {
    expect($status->getLabel())
        ->toBe($label)
        ->and($status->getColor())
        ->toBe($color);
})->with([
    'not tracked' => [SourceReviewStatus::NotTracked, 'Not tracked', 'gray'],
    'current' => [SourceReviewStatus::Current, 'Current', 'success'],
    'review due' => [SourceReviewStatus::ReviewDue, 'Review due', 'warning'],
]);
