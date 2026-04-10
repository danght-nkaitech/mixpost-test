<?php

namespace Inovector\MixpostEnterprise\Http\Base\Requests\Dashboard\Main;

use Illuminate\Foundation\Http\FormRequest;
use Inovector\Mixpost\Concerns\UsesAuth;
use Inovector\MixpostEnterprise\Models\Invitation;

class DeclineInvitation extends FormRequest
{
    use UsesAuth;

    protected ?Invitation $invitation = null;

    public function authorize(): bool
    {
        return $this->invitation()->isForUser(self::getAuthGuard()->user());
    }

    public function rules(): array
    {
        return [];
    }

    public function handle(): void
    {
        $this->invitation()->delete();
    }

    protected function invitation(): Invitation
    {
        if (! $this->invitation) {
            return $this->invitation = Invitation::firstOrFailByUuid($this->route('invitation'))->load('workspace');
        }

        return $this->invitation;
    }
}
