<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

arch('keeps storage, mail and HTTP calls out of observers')
    ->expect('App\Observers')
    ->not->toUse([Storage::class, Mail::class, Http::class]);
