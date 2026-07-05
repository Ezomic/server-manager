<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ServiceActionRequest extends FormRequest
{
    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(['systemd', 'docker'])],
            'name' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9@_.:-]+$/'],
            'action' => ['required', Rule::in(['start', 'stop', 'restart'])],
        ];
    }
}
