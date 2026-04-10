<?php

namespace Inovector\MixpostEnterprise\Http\Base\Requests\Dashboard\Workspace;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Inovector\Mixpost\Facades\WorkspaceManager;
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

    public function handle(): bool
    {
        return WorkspaceManager::current()->update([
            'name' => $this->input('name'),
            'hex_color' => Str::after($this->input('hex_color'), '#'),
        ]);
    }
}
