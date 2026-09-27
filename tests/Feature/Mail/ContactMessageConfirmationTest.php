<?php

use App\Mail\ContactMessageConfirmation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

pest()->use(RefreshDatabase::class);

it('encrypts sensitive content in the queued mail payload', function () {
    $mail = new ContactMessageConfirmation('Private Sender', 'consulting', null, 'Confidential message');

    Mail::to('recipient@example.test')->queue($mail->onConnection('database'));

    $payload = DB::table('jobs')->sole()
        ->payload;
    if (! is_string($payload)) {
        throw new RuntimeException('Expected a JSON queue payload.');
    }
    $command = data_get(json_decode($payload, true, flags: JSON_THROW_ON_ERROR), 'data.command');
    if (! is_string($command)) {
        throw new RuntimeException('Expected a serialized queued command.');
    }

    expect($command)->not->toContain('Confidential message')
        ->and(Crypt::decrypt($command))
        ->toContain('Confidential message');
});
