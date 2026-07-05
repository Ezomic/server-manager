<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TailLogRequest extends FormRequest
{
    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'path' => ['required', 'string', 'max:1024', 'regex:#^/[A-Za-z0-9_./-]+$#'],
            'lines' => ['sometimes', 'integer', 'between:10,1000'],
        ];
    }
}
