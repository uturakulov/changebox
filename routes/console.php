<?php

use App\Services\BatchService;
use Illuminate\Support\Facades\Schedule;

Schedule::call(function () {
    BatchService::createBatches();
})->everyMinute();
