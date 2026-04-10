<?php

namespace Inovector\Mixpost\Http\Base\Requests\Workspace;

use Illuminate\Foundation\Http\FormRequest;
use Inovector\Mixpost\Models\Template;

class StoreTemplate extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'content.*.body' => ['nullable', 'string'],
            'content.*.media' => ['array'],
            'content.*.media.*' => ['integer'],
        ];
    }

    public function handle()
    {
        return Template::create([
            'name' => $this->input('name'),
            'content' => $this->input('content'),
        ]);
    }
}
