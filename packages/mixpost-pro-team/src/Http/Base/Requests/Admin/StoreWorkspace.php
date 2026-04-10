<?php

namespace Inovector\Mixpost\Http\Base\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inovector\Mixpost\Enums\WorkspaceUserRole;
use Inovector\Mixpost\Models\Workspace;
use Inovector\Mixpost\Rules\HexRule;

class StoreWorkspace extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:60'],
            'hex_color' => ['required', new HexRule],
        ];
    }

    public function handle(): ?Workspace
    {
        $workspace = null;

        DB::transaction(function () use (&$workspace) {
            $workspace = Workspace::create([
                'name' => $this->input('name'),
                'hex_color' => Str::after($this->input('hex_color'), '#'),
            ]);

            $workspace->attachUser(
                id: Auth::id(),
                role: WorkspaceUserRole::ADMIN,
                canApprove: true
            );
        });

        return $workspace;
    }
}
