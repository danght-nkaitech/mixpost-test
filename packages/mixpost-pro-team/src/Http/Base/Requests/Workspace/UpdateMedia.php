<?php

namespace Inovector\Mixpost\Http\Base\Requests\Workspace;

use Illuminate\Foundation\Http\FormRequest;
use Inovector\Mixpost\Models\Media;

class UpdateMedia extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['string', 'max:255', 'nullable'],
            'alt_text' => ['string', 'max:500', 'nullable'],
        ];
    }

    public function handle(): int
    {
        $media = Media::where('uuid', $this->route('item'))->firstOrFail();

        $data = null;

        if ($this->has('alt_text')) {
            $data = [
                'data->alt_text' => $this->input('alt_text'),
            ];
        }

        if ($this->has('name')) {
            $data['name'] = $this->input('name');
        }

        return $media->update($data);
    }
}
