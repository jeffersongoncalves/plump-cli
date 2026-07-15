<?php

use App\Services\PlumbClient;

it('lists scan history', function () {
    $client = Mockery::mock(PlumbClient::class);
    $client->shouldReceive('getHistory')
        ->once()
        ->with('vendor', 'pkg', '-scanned_at', 20)
        ->andReturn([
            'data' => [
                ['id' => 2, 'scanned_at' => '2026-07-01', 'reference_version' => '1.1.0', 'scores' => ['composite' => 90, 'security' => 91, 'maintenance' => 89, 'ecosystem' => 88]],
                ['id' => 1, 'scanned_at' => '2026-06-01', 'reference_version' => '1.0.0', 'scores' => ['composite' => 85, 'security' => 86, 'maintenance' => 84, 'ecosystem' => 83]],
            ],
        ]);

    $this->app->instance(PlumbClient::class, $client);

    $this->artisan('history vendor/pkg')
        ->expectsOutputToContain('1.1.0')
        ->expectsOutputToContain('1.0.0')
        ->expectsOutputToContain('2 scan(s)')
        ->assertExitCode(0);
});

it('warns when there is no scan history', function () {
    $client = Mockery::mock(PlumbClient::class);
    $client->shouldReceive('getHistory')->once()->andReturn(['data' => []]);

    $this->app->instance(PlumbClient::class, $client);

    $this->artisan('history vendor/pkg')->assertExitCode(0);
});

it('clamps the page size option to 100', function () {
    $client = Mockery::mock(PlumbClient::class);
    $client->shouldReceive('getHistory')->once()->with('vendor', 'pkg', '-scanned_at', 100)->andReturn(['data' => []]);

    $this->app->instance(PlumbClient::class, $client);

    $this->artisan('history vendor/pkg --limit=500')->assertExitCode(0);
});
