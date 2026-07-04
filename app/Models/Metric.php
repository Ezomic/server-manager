<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\MetricFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['server_id', 'cpu_percent', 'memory_used_mb', 'memory_total_mb', 'disk_used_mb', 'disk_total_mb', 'load_avg', 'recorded_at'])]
class Metric extends Model
{
    /** @use HasFactory<MetricFactory> */
    use HasFactory;

    /** @return BelongsTo<Server, $this> */
    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    protected function casts(): array
    {
        return [
            'cpu_percent' => 'float',
            'load_avg' => 'float',
            'recorded_at' => 'immutable_datetime',
        ];
    }
}
