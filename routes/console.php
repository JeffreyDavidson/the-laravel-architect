<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('youtube:stats')->daily()->withoutOverlapping()->onOneServer();
Schedule::command('youtube:sync')->weekly()->withoutOverlapping()->onOneServer();
