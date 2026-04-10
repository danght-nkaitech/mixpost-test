<?php

namespace Inovector\Mixpost\Http\Base\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Inovector\Mixpost\Models\Block;
use Inovector\Mixpost\Rules\ResourceStatusRule;

class StoreBlock extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'max:255'],
            'module' => ['required'],
            'content' => ['required', 'array'],
            'status' => ['required', 'integer', new ResourceStatusRule],
        ];
    }

    public function handle(): Block
    {
        return Block::create([
            'name' => $this->input('name'),
            'module' => $this->input('module'),
            'content' => $this->input('content'),
            'status' => $this->input('status'),
        ]);
    }
}
