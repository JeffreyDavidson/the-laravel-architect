<?php

use App\Enums\SubscriberStatus;
use Filament\Support\Icons\Heroicon;

it('exposes explicit labels, colors and icons for each subscriber status', function () {
    $details = [];

    foreach (SubscriberStatus::cases() as $case) {
        $details[$case->value] = [$case->getLabel(), $case->getColor(), $case->getIcon()];
    }

    expect($details)
        ->toBe([
            'active' => ['Active', 'success', Heroicon::OutlinedCheckCircle],
            'pending' => ['Pending confirmation', 'warning', Heroicon::OutlinedClock],
            'unsubscribed' => ['Unsubscribed', 'gray', Heroicon::OutlinedXCircle],
            'suppressed' => ['Suppressed', 'danger', Heroicon::OutlinedNoSymbol],
        ]);
});
