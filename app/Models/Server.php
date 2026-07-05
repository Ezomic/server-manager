<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\ServerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string $hostname
 * @property int $port
 * @property string $ssh_user
 * @property int|null $ssh_key_id
 * @property string $type
 * @property string|null $provider
 * @property array<int, string>|null $tags
 * @property string|null $notes
 * @property string $status
 * @property CarbonImmutable|null $last_seen_at
 */
#[Fillable(['name', 'hostname', 'port', 'ssh_user', 'ssh_key_id', 'type', 'provider', 'tags', 'notes'])]
#[Hidden(['agent_token'])]
class Server extends Model
{
    /** @use HasFactory<ServerFactory> */
    use HasFactory;

    /** @return BelongsTo<SshKey, $this> */
    public function sshKey(): BelongsTo
    {
        return $this->belongsTo(SshKey::class);
    }

    /** @return HasMany<Metric, $this> */
    public function metrics(): HasMany
    {
        return $this->hasMany(Metric::class);
    }

    /** @return HasMany<DeploymentScript, $this> */
    public function deploymentScripts(): HasMany
    {
        return $this->hasMany(DeploymentScript::class);
    }

    /** @return HasMany<Deployment, $this> */
    public function deployments(): HasMany
    {
        return $this->hasMany(Deployment::class);
    }

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'last_seen_at' => 'immutable_datetime',
        ];
    }
}
