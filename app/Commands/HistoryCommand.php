<?php

namespace App\Commands;

use App\Concerns\ParsesPackageName;
use App\Exceptions\PlumbApiException;
use App\Services\PlumbClient;
use LaravelZero\Framework\Commands\Command;

class HistoryCommand extends Command
{
    use ParsesPackageName;

    protected $signature = 'history
        {package : Package name (vendor/name)}
        {--sort=-scanned_at : Sort order for the scan list}
        {--limit=20 : Page size (max 100)}';

    protected $description = 'List historical scans for a package on Plumb';

    public function handle(PlumbClient $client): int
    {
        $name = (string) $this->argument('package');
        $parts = $this->parsePackageName($name);

        if ($parts === null) {
            $this->components->error("Invalid package name <comment>{$name}</comment>. Expected format: vendor/name.");

            return self::FAILURE;
        }

        [$vendor, $package] = $parts;
        $sort = (string) $this->option('sort');
        $pageSize = min(100, max(1, (int) $this->option('limit')));

        try {
            $response = $client->getHistory($vendor, $package, $sort, $pageSize);
        } catch (PlumbApiException $e) {
            $this->components->error($e->getMessage());

            return self::FAILURE;
        }

        $scans = $response['data'] ?? [];

        if (! is_array($scans) || $scans === []) {
            $this->components->warn("No scan history for <comment>{$name}</comment>.");

            return self::SUCCESS;
        }

        $rows = [];

        foreach ($scans as $scan) {
            $scan = is_array($scan) ? $scan : [];
            $scores = is_array($scan['scores'] ?? null) ? $scan['scores'] : [];

            $rows[] = [
                (string) ($scan['id'] ?? '-'),
                (string) ($scan['scanned_at'] ?? '-'),
                (string) ($scan['reference_version'] ?? '-'),
                (string) ($scores['composite'] ?? '-'),
                (string) ($scores['security'] ?? '-'),
                (string) ($scores['maintenance'] ?? '-'),
                (string) ($scores['ecosystem'] ?? '-'),
            ];
        }

        $this->table(['ID', 'Scanned at', 'Version', 'Composite', 'Security', 'Maintenance', 'Ecosystem'], $rows);

        $this->components->info(count($rows)." scan(s) for <comment>{$name}</comment>.");

        return self::SUCCESS;
    }
}
