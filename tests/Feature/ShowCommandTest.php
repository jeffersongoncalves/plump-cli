<?php

use App\Exceptions\PlumbApiException;
use App\Services\PlumbClient;

it('shows a package\'s scores', function () {
    $client = Mockery::mock(PlumbClient::class);
    $client->shouldReceive('getPackage')
        ->once()
        ->with('vendor', 'pkg')
        ->andReturn([
            'data' => [
                'composer_name' => 'vendor/pkg',
                'description' => 'A package',
                'canonical_repository_url' => 'https://github.com/vendor/pkg',
                'is_abandoned' => false,
                'last_scanned_at' => '2026-07-01T00:00:00Z',
                'latest_scan' => [
                    'scanned_at' => '2026-07-01T00:00:00Z',
                    'reference_version' => '1.0.0',
                    'scores' => ['composite' => 92, 'security' => 95, 'maintenance' => 90, 'ecosystem' => 88],
                ],
            ],
            '_http_status' => 200,
        ]);

    $this->app->instance(PlumbClient::class, $client);

    $this->artisan('show vendor/pkg')
        ->expectsOutputToContain('vendor/pkg')
        ->expectsOutputToContain('92')
        ->assertExitCode(0);
});

it('rejects an invalid package name', function () {
    $this->artisan('show invalid')
        ->assertExitCode(1);
});

it('surfaces API errors', function () {
    $client = Mockery::mock(PlumbClient::class);
    $client->shouldReceive('getPackage')->once()->andThrow(
        PlumbApiException::fromResponse(404, [])
    );

    $this->app->instance(PlumbClient::class, $client);

    $this->artisan('show vendor/missing')->assertExitCode(1);
});
