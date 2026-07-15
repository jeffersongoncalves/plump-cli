<?php

namespace App\Commands;

use App\Concerns\ParsesPackageName;
use App\Exceptions\PlumbApiException;
use App\Services\PlumbClient;
use LaravelZero\Framework\Commands\Command;

class ScanCommand extends Command
{
    use ParsesPackageName;

    protected $signature = 'scan
        {package : Package name (vendor/name)}';

    protected $description = 'Trigger a Plumb scan for a package (rate limited: 3 requests/15 minutes)';

    public function handle(PlumbClient $client): int
    {
        $name = (string) $this->argument('package');
        $parts = $this->parsePackageName($name);

        if ($parts === null) {
            $this->components->error("Invalid package name <comment>{$name}</comment>. Expected format: vendor/name.");

            return self::FAILURE;
        }

        [$vendor, $package] = $parts;

        try {
            $response = $client->triggerScan($vendor, $package);
        } catch (PlumbApiException $e) {
            $this->components->error($e->getMessage());

            return self::FAILURE;
        }

        if (($response['_http_status'] ?? null) === 202) {
            $this->components->info("Scan queued for <comment>{$name}</comment>.");
        } else {
            $this->components->info("Cached scan for <comment>{$name}</comment> is already fresh.");
        }

        return self::SUCCESS;
    }
}
