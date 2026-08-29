<?php

use App\Models\Subscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('does not mass assign its verification token hash', function () {
    $subscriber = new Subscriber;

    $subscriber->fill([
        'email' => 'reader@example.com',
        'verification_token_hash' => hash('sha256', 'secret-token'),
    ]);

    expect($subscriber->email)->toBe('reader@example.com')
        ->and($subscriber->getAttributes())->not->toHaveKey('verification_token_hash');
});

it('hides its verification token hash from serialization', function () {
    $subscriber = new Subscriber;
    $subscriber->email = 'reader@example.com';
    $subscriber->verification_token_hash = hash('sha256', 'secret-token');

    expect($subscriber->toArray())
        ->toHaveKey('email', 'reader@example.com')
        ->not->toHaveKey('verification_token_hash');
});
