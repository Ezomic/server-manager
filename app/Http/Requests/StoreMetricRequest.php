<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMetricRequest extends FormRequest
{
    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'cpu_percent' => ['required', 'numeric', 'between:0,100'],
            'memory_used_mb' => ['required', 'integer', 'min:0'],
            'memory_total_mb' => ['required', 'integer', 'min:1'],
            'disk_used_mb' => ['required', 'integer', 'min:0'],
            'disk_total_mb' => ['required', 'integer', 'min:1'],
            'load_avg' => ['required', 'numeric', 'min:0'],
        ];
    }

    /** @return array<string, float|int> */
    public function metrics(): array
    {
        return [
            'cpu_percent' => $this->float('cpu_percent'),
            'memory_used_mb' => $this->integer('memory_used_mb'),
            'memory_total_mb' => $this->integer('memory_total_mb'),
            'disk_used_mb' => $this->integer('disk_used_mb'),
            'disk_total_mb' => $this->integer('disk_total_mb'),
            'load_avg' => $this->float('load_avg'),
        ];
    }
}
