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
    $connection->beginTransaction();

    try {
        $connection->select('SELECT pg_advisory_xact_lock(?)', [$lockKey]);
        $exitCode = $this->call('migrate', ['--force' => true]);

        if ($exitCode !== 0) {
            $connection->rollBack();

            return $exitCode;
        }

        $connection->commit();

        return 0;
    } catch (Throwable $exception) {
        if ($connection->transactionLevel() > 0) {
            $connection->rollBack();
        }

        throw $exception;
    }
})->purpose('Run deployment migrations with a PostgreSQL advisory lock');
