<?php

namespace App\Http\Requests;

use Common\Core\BaseFormRequest;

class CrupdateShareableLinkRequest extends BaseFormRequest
{
    public function messages(): array
    {
        return [
            'expiresAt.date' => 'This is not a valid date.',
        ];
    }

    public function rules(): array
    {
        return [
            'allowDownload' => 'boolean',
            'allowEdit' => 'boolean',
            'expiresAt' => 'nullable|date',
            'password' => 'nullable|string',
        ];
    }
}
