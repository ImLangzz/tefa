<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('deployment:migrate', function () {
    $lockKey = 74658213091;
    $connection = DB::connection('pgsql');
    $connection->select('SELECT pg_advisory_lock(?)', [$lockKey]);

    try {
        return $this->call('migrate', ['--force' => true]);
    } finally {
        $connection->select('SELECT pg_advisory_unlock(?)', [$lockKey]);
    }
})->purpose('Run deployment migrations with a PostgreSQL advisory lock');
