<?php

namespace Pterodactyl\Http\Requests\Admin;

class AnnouncementFormRequest extends AdminFormRequest
{
    public function rules(): array
    {
        return [
            'title' => 'required|string|between:1,191',
            'content' => 'required|string|max:2000',
            'is_active' => 'sometimes|boolean',
        ];
    }
}
