<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schedule;

Schedule::command('youtube:stats')->daily();
Schedule::command('youtube:sync')->weekly();
