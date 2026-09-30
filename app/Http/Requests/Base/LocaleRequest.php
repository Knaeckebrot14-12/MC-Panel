<?php

namespace Pterodactyl\Http\Requests\Base;

use Illuminate\Foundation\Http\FormRequest;

class LocaleRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'locale' => ['required', 'string', 'regex:/^[a-z][a-z]$/'],
            // Translation namespaces map directly to lang file paths, which can
            // be nested (e.g. "dashboard/account") or use underscores (e.g.
            // "create_server") — not just single flat lowercase words.
            'namespace' => ['required', 'string', 'regex:/^[a-z0-9_\/]{1,191}$/'],
        ];
    }
}
