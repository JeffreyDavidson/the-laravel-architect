<?php

use Illuminate\Mail\Mailable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Mail;

arch('names mailables after their purpose with a Mail suffix')
    ->expect('App\Mail')
    ->classes()
    ->toHaveSuffix('Mail')
    ->toExtend(Mailable::class)
    ->ignoring('App\Mail\Concerns');

arch('names notifications after their purpose with a Notification suffix')
    ->expect('App\Notifications')
    ->classes()
    ->toHaveSuffix('Notification')
    ->toExtend(Notification::class);

arch('sends mail through the injected Mailer contract instead of the facade')
    ->expect('App')
    ->not->toUse(Mail::class);
