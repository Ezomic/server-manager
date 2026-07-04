<?php

declare(strict_types=1);

namespace App\Services\Ssh;

class SshResult
{
    public function __construct(
        public readonly bool $successful,
        public readonly string $output,
        public readonly string $errorOutput = '',
    ) {}
}
