<?php

use App\Exceptions\PlumbApiException;
use App\Services\PlumbClient;

it('reports a queued scan', function () {
    $client = Mockery::mock(PlumbClient::class);
    $client->shouldReceive('triggerScan')->once()->with('vendor', 'pkg')->andReturn(['_http_status' => 202]);

    $this->app->instance(PlumbClient::class, $client);

    $this->artisan('scan vendor/pkg')
        ->expectsOutputToContain('queued')
        ->assertExitCode(0);
});

it('reports a cached scan', function () {
    $client = Mockery::mock(PlumbClient::class);
    $client->shouldReceive('triggerScan')->once()->with('vendor', 'pkg')->andReturn(['_http_status' => 200]);

    $this->app->instance(PlumbClient::class, $client);

    $this->artisan('scan vendor/pkg')
        ->expectsOutputToContain('fresh')
        ->assertExitCode(0);
});

it('surfaces a budget-exhausted error', function () {
    $client = Mockery::mock(PlumbClient::class);
    $client->shouldReceive('triggerScan')->once()->andThrow(
        PlumbApiException::fromResponse(503, [], '3600')
    );

    $this->app->instance(PlumbClient::class, $client);

    $this->artisan('scan vendor/pkg')
        ->expectsOutputToContain('Retry after 3600s')
        ->assertExitCode(1);
});
