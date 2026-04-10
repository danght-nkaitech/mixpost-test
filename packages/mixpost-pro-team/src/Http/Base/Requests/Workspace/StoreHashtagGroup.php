<?php

namespace Inovector\Mixpost\Http\Base\Requests\Workspace;

use Illuminate\Foundation\Http\FormRequest;
use Inovector\Mixpost\Models\HashtagGroup;

class StoreHashtagGroup extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string', 'max:3000'],
        ];
    }

    public function handle()
    {
        return HashtagGroup::create([
            'name' => $this->input('name'),
            'content' => $this->input('content'),
        ]);
    }
}
