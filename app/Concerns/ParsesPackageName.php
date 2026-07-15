<?php

namespace App\Concerns;

trait ParsesPackageName
{
    /**
     * Split a "vendor/name" package argument into its two parts.
     *
     * @return array{0: string, 1: string}|null
     */
    private function parsePackageName(string $package): ?array
    {
        $parts = explode('/', $package, 2);

        if (count($parts) !== 2 || $parts[0] === '' || $parts[1] === '') {
            return null;
        }

        return [$parts[0], $parts[1]];
    }
}
