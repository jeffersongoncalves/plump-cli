<?php

use App\Concerns\ParsesPackageName;

it('splits a valid vendor/name package argument', function () {
    $subject = new class
    {
        use ParsesPackageName;

        public function call(string $package): ?array
        {
            return $this->parsePackageName($package);
        }
    };

    expect($subject->call('vendor/pkg'))->toBe(['vendor', 'pkg'])
        ->and($subject->call('vendor'))->toBeNull()
        ->and($subject->call('/pkg'))->toBeNull()
        ->and($subject->call('vendor/'))->toBeNull();
});
