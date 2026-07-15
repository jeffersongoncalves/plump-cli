<?php

namespace App\Services;

use App\Exceptions\PlumbApiException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;

class PlumbClient
{
    private const BASE_URL = 'https://plumbphp.dev/api/v1';

    public function __construct(
        private readonly Client $client = new Client(['timeout' => 30]),
    ) {}

    /**
     * Fetch a package and its most recent completed scan.
     * GET /packages/{vendor}/{name} — 120 requests/minute.
     *
     * @return array<string, mixed>
     */
    public function getPackage(string $vendor, string $name): array
    {
        return $this->request('GET', "/packages/{$vendor}/{$name}");
    }

    /**
     * List historical scans for a package, newest first.
     * GET /packages/{vendor}/{name}/history — 120 requests/minute.
     *
     * @return array<string, mixed>
     */
    public function getHistory(string $vendor, string $name, ?string $sort = null, int $pageSize = 20): array
    {
        $query = array_filter([
            'sort' => $sort,
            'page[size]' => $pageSize,
        ], static fn ($value): bool => $value !== null);

        return $this->request('GET', "/packages/{$vendor}/{$name}/history", $query);
    }

    /**
     * Queue a scan (202) or return an already-fresh cached scan (200).
     * POST /packages/{vendor}/{name} — 3 requests/15 minutes.
     *
     * The HTTP status code is returned under the "_http_status" key so
     * callers can tell a freshly queued scan apart from a cached result.
     *
     * @return array<string, mixed>
     */
    public function triggerScan(string $vendor, string $name): array
    {
        return $this->request('POST', "/packages/{$vendor}/{$name}");
    }

    /**
     * @param  array<string, int|string>  $query
     * @return array<string, mixed>
     */
    private function request(string $method, string $path, array $query = []): array
    {
        $url = self::BASE_URL.$path;

        if ($query !== []) {
            $url .= '?'.http_build_query($query);
        }

        try {
            $response = $this->client->request($method, $url, [
                'headers' => ['Accept' => 'application/json'],
            ]);
        } catch (RequestException $e) {
            $body = $e->getResponse() ? json_decode((string) $e->getResponse()->getBody(), true) : null;

            throw PlumbApiException::fromResponse(
                $e->getResponse()?->getStatusCode() ?? 0,
                is_array($body) ? $body : [],
                $e->getResponse()?->getHeaderLine('Retry-After') ?: null,
            );
        } catch (GuzzleException $e) {
            throw new PlumbApiException('Plumb request failed: '.$e->getMessage());
        }

        $data = json_decode((string) $response->getBody(), true);
        $data = is_array($data) ? $data : [];
        $data['_http_status'] = $response->getStatusCode();

        return $data;
    }
}
