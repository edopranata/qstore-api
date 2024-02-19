<?php

use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('app:install', function () {
    $this->info("Rollback and Migrate table");
    Artisan::call('migrate:fresh', [
        '--force' => true
    ]);
    $this->info("Migrating complete");
})->purpose('Migrate db Installation');

Artisan::command('app:sample-data', function () {
    $this->info("Dumping dummy data");
    Artisan::call('db:seed', [
        '--class' => 'DatabaseSeeder'
    ]);

    Artisan::call('db:seed', [
        '--class' => 'SampleMaster'
    ]);
    $this->info("Dumping data complete");
    $this->info("User: administrator");
    $this->info("Pass: password");
})->purpose('Dump sample data');

Artisan::command('app:fresh-install', function () {
    Artisan::call('migrate:fresh', [
        '--force' => true
    ]);

    Artisan::call('db:seed', [
        '--class' => 'DatabaseSeeder'
    ]);

    Artisan::call('db:seed', [
        '--class' => 'SampleMaster'
    ]);
})->purpose('Migrate db Installation and dump dummy data');

Artisan::command('app:sample-transaction', function () {
    $this->info("Dump sample transaction");
    Artisan::call('db:seed', [
        '--class' => 'SampleTransaction',
        '--force' => true,
    ]);
    $this->info("Dumping complete");
})->purpose('Dump sample transaction');
