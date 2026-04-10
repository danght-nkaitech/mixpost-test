<?php

namespace Inovector\Mixpost\Http\Base\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class RecreateMastodonService extends FormRequest
{
    public function rules(): array
    {
        return [
            'server' => ['required', 'string', 'max:255'],
        ];
    }
}
