<?php

use Illuminate\Support\Facades\Schedule;
use App\Console\Commands\PubblicaPubblicazioni;

Schedule::command(PubblicaPubblicazioni::class)->everyMinute();
