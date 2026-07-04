<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\SshKey;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<SshKey> */
class SshKeyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'public_key' => 'ssh-ed25519 AAAA'.fake()->sha256(),
            'private_key' => '-----BEGIN OPENSSH PRIVATE KEY-----'.PHP_EOL.fake()->sha256().PHP_EOL.'-----END OPENSSH PRIVATE KEY-----',
        ];
    }
}
