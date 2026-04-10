<?php

namespace Inovector\Mixpost\Http\Base\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Inovector\Mixpost\Models\Workspace;
use Inovector\Mixpost\Rules\HexRule;

class UpdateWorkspace extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:60'],
            'hex_color' => ['required', new HexRule],
        ];
    }

    public function handle()
    {
        return Workspace::firstOrFailByUuid($this->route('workspace'))->update([
            'name' => $this->input('name'),
            'hex_color' => Str::after($this->input('hex_color'), '#'),
        ]);
    }
}
