<?php

use App\Data\ContactMessageData;
use App\Enums\ContactBudget;
use App\Enums\ContactType;

it('retains typed contact fields through serialization', function () {
    $data = new ContactMessageData(
        name: 'Jane Doe',
        email: 'jane@example.com',
        type: ContactType::Consulting,
        budget: ContactBudget::Medium,
        message: 'A project inquiry.',
    );

    $restored = unserialize(serialize($data));

    if (! $restored instanceof ContactMessageData) {
        throw new RuntimeException('Expected contact data after serialization.');
    }

    expect($restored)->toEqual($data)
        ->and($restored->type)->toBe(ContactType::Consulting)
        ->and($restored->budget)->toBe(ContactBudget::Medium);
});
