<?php

namespace Inovector\Mixpost\Http\Base\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Inovector\Mixpost\Concerns\UsesUserModel;
use Inovector\Mixpost\Models\Workspace;

class DetachWorkspaceUser extends FormRequest
{
    use UsesUserModel;

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'exists:'.app(self::getUserClass())->getTable().',id'],
        ];
    }

    public function handle()
    {
        $workspace = Workspace::firstOrFailByUuid($this->route('workspace'));

        $workspace->users()->detach($this->input('user_id'));
    }
}
