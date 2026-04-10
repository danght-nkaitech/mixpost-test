<?php

namespace Inovector\Mixpost\Http\Api\Requests\Workspace;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Inovector\Mixpost\Models\Tag;
use Inovector\Mixpost\Rules\HexRule;

class UpdateTag extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'hex_color' => ['required', new HexRule],
        ];
    }

    public function handle(): bool
    {
        $record = Tag::firstOrFailByUuid($this->route('tag'));

        return $record->update([
            'name' => $this->input('name'),
            'hex_color' => Str::after($this->input('hex_color'), '#'),
        ]);
    }
}
