<?php

use App\Providers\AppServiceProvider;

return [
    'name' => 'Plump',
    'version' => app('git.version'),
    'env' => 'development',
    'providers' => [
        AppServiceProvider::class,
    ],
];
