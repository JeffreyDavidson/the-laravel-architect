<?php

use App\Providers\AppServiceProvider;
use App\Providers\Filament\AdminPanelProvider;
use App\Providers\MonitoringServiceProvider;

return [
    AppServiceProvider::class,
    MonitoringServiceProvider::class,
    AdminPanelProvider::class,
];
