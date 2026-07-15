<?php

use App\Exceptions\PlumbApiException;

it('uses a string message verbatim', function () {
    $e = PlumbApiException::fromResponse(400, ['message' => 'Bad request.']);

    expect($e->getMessage())->toBe('Bad request.')
        ->and($e->statusCode)->toBe(400);
});

it('falls back to a default message per status code', function () {
    expect(PlumbApiException::fromResponse(404, [])->getMessage())->toBe('Package not found.')
        ->and(PlumbApiException::fromResponse(429, [])->getMessage())->toBe('Rate limit exceeded (120 GET requests/minute).')
        ->and(PlumbApiException::fromResponse(503, [])->getMessage())->toBe('Daily scan budget exhausted.')
        ->and(PlumbApiException::fromResponse(500, [])->getMessage())->toBe('Plumb API request failed.');
});

it('appends the stale_since detail on 422 responses', function () {
    $e = PlumbApiException::fromResponse(422, ['message' => 'Scan results are stale.', 'stale_since' => '2026-07-01T00:00:00Z']);

    expect($e->getMessage())->toBe('Scan results are stale. Stale since 2026-07-01T00:00:00Z.');
});

it('parses and appends the Retry-After header', function () {
    $e = PlumbApiException::fromResponse(503, [], '900');

    expect($e->getMessage())->toBe('Daily scan budget exhausted. Retry after 900s.')
        ->and($e->retryAfter)->toBe(900);
});

it('ignores a non-numeric Retry-After header', function () {
    $e = PlumbApiException::fromResponse(503, [], 'not-a-number');

    expect($e->retryAfter)->toBeNull()
        ->and($e->getMessage())->toBe('Daily scan budget exhausted.');
});
