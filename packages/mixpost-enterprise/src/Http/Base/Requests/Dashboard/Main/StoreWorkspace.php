<?php

namespace Inovector\MixpostEnterprise\Http\Base\Requests\Dashboard\Main;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inovector\Mixpost\Enums\WorkspaceUserRole;
use Inovector\Mixpost\Rules\HexRule;
use Inovector\MixpostEnterprise\Events\Workspace\WorkspaceCreated;
use Inovector\MixpostEnterprise\Models\Workspace;

class StoreWorkspace extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:60'],
            'hex_color' => ['required', new HexRule],
        ];
    }

    public function handle(): Workspace
    {
        $workspace = DB::transaction(function () {
            $record = Workspace::create([
                'name' => $this->input('name'),
                'hex_color' => Str::after($this->input('hex_color'), '#'),
            ]);

            $record->attachUser(
                id: Auth::id(),
                role: WorkspaceUserRole::ADMIN,
                canApprove: true
            );

            $record->saveOwner(Auth::user());

            return $record;
        });

        WorkspaceCreated::dispatch($workspace);

        return $workspace;
    }
}
