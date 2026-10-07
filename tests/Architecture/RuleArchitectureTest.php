<?php

use Illuminate\Contracts\Validation\ValidationRule;

arch('makes validation rules final ValidationRule classes')
    ->expect('App\Rules')
    ->classes()
    ->toBeFinal()
    ->toImplement(ValidationRule::class);
