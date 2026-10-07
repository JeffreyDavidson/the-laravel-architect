<?php

arch('makes application exceptions final Exception subclasses')
    ->expect('App\Exceptions')
    ->classes()
    ->toBeFinal()
    ->toExtend(Exception::class);
