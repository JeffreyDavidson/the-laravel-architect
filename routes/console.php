<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('youtube:stats')->daily();
Schedule::command('youtube:sync')->weekly();
