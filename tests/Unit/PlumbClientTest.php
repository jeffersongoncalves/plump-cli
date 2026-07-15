<?php

use App\Exceptions\PlumbApiException;
use App\Services\PlumbClient;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

it('decodes a package response and exposes the HTTP status', function () {
    $handler = HandlerStack::create(new MockHandler([
        new Response(200, [], json_encode(['data' => ['composer_name' => 'vendor/pkg']])),
    ]));

    $client = new PlumbClient(new Client(['handler' => $handler]));
    $response = $client->getPackage('vendor', 'pkg');

    expect($response['data']['composer_name'])->toBe('vendor/pkg')
        ->and($response['_http_status'])->toBe(200);
});

it('distinguishes a queued scan (202) from a cached one (200)', function () {
    $handler = HandlerStack::create(new MockHandler([
        new Response(202, [], json_encode(['data' => ['status' => 'queued']])),
    ]));

    $client = new PlumbClient(new Client(['handler' => $handler]));
    $response = $client->triggerScan('vendor', 'pkg');

    expect($response['_http_status'])->toBe(202);
});

it('throws with the Retry-After header when the scan budget is exhausted', function () {
    $handler = HandlerStack::create(new MockHandler([
        new Response(503, ['Retry-After' => '3600'], json_encode(['message' => 'Daily scan budget exhausted.'])),
    ]));

    $client = new PlumbClient(new Client(['handler' => $handler]));

    try {
        $client->triggerScan('vendor', 'pkg');
        test()->fail('Expected PlumbApiException');
    } catch (PlumbApiException $e) {
        expect($e->statusCode)->toBe(503)
            ->and($e->retryAfter)->toBe(3600);
    }
});

it('throws a 404 as a package-not-found exception', function () {
    $handler = HandlerStack::create(new MockHandler([
        new Response(404, [], json_encode([])),
    ]));

    $client = new PlumbClient(new Client(['handler' => $handler]));

    try {
        $client->getPackage('vendor', 'missing');
        test()->fail('Expected PlumbApiException');
    } catch (PlumbApiException $e) {
        expect($e->statusCode)->toBe(404)
            ->and($e->getMessage())->toBe('Package not found.');
    }
});
