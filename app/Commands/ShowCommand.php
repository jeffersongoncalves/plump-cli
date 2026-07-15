<?php

namespace App\Commands;

use App\Concerns\ParsesPackageName;
use App\Exceptions\PlumbApiException;
use App\Services\PlumbClient;
use LaravelZero\Framework\Commands\Command;

class ShowCommand extends Command
{
    use ParsesPackageName;

    protected $signature = 'show
        {package : Package name (vendor/name)}';

    protected $description = 'Show a package\'s quality scores and latest scan from Plumb';

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
            $response = $client->getPackage($vendor, $package);
        } catch (PlumbApiException $e) {
            $this->components->error($e->getMessage());

            return self::FAILURE;
        }

        $data = $response['data'] ?? $response;

        $this->components->twoColumnDetail('Package', (string) ($data['composer_name'] ?? $name));
        $this->components->twoColumnDetail('Description', (string) ($data['description'] ?? '-'));
        $this->components->twoColumnDetail('Repository', (string) ($data['canonical_repository_url'] ?? $data['repository_url'] ?? '-'));
        $this->components->twoColumnDetail('Abandoned', ($data['is_abandoned'] ?? false) ? 'yes' : 'no');
        $this->components->twoColumnDetail('Last scanned', (string) ($data['last_scanned_at'] ?? '-'));

        $scan = $data['latest_scan'] ?? null;

        if (! is_array($scan)) {
            $this->components->warn("No completed scan yet for <comment>{$name}</comment>. Run: plump scan {$name}");

            return self::SUCCESS;
        }

        $scores = is_array($scan['scores'] ?? null) ? $scan['scores'] : [];

        $this->newLine();
        $this->components->twoColumnDetail('Scanned at', (string) ($scan['scanned_at'] ?? '-'));
        $this->components->twoColumnDetail('Reference', (string) ($scan['reference_version'] ?? $scan['reference_commit'] ?? '-'));
        $this->components->twoColumnDetail('Composite score', (string) ($scores['composite'] ?? '-'));
        $this->components->twoColumnDetail('Security score', (string) ($scores['security'] ?? '-'));
        $this->components->twoColumnDetail('Maintenance score', (string) ($scores['maintenance'] ?? '-'));
        $this->components->twoColumnDetail('Ecosystem score', (string) ($scores['ecosystem'] ?? '-'));

        return self::SUCCESS;
    }
}
