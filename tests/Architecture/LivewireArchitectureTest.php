<?php

arch('leaves reads to view models instead of calling queries from Livewire components')
    ->expect('App\Livewire')
    ->not->toUse('App\Queries');
