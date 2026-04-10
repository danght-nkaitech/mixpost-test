<?php

namespace Inovector\Mixpost\Http\Base\Requests\Main;

use Illuminate\Foundation\Http\FormRequest;
use Inovector\Mixpost\Support\FetchUrlCard;

class ExtractUrlMeta extends FormRequest
{
    public function rules(): array
    {
        return [
            'url' => ['required', 'url'],
        ];
    }

    public function handle(): array
    {
        return (new FetchUrlCard)($this->get('url'));
    }
}
