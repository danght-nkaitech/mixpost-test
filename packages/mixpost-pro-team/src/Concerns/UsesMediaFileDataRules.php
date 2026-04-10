<?php

namespace Inovector\Mixpost\Concerns;

trait UsesMediaFileDataRules
{
    protected function mediaFileDataRules(): array
    {
        return [
            'alt_text' => ['string', 'max:255', 'nullable'],
            'adobe_express_doc_id' => ['string', 'max:255', 'nullable'],
        ];
    }

    protected function fileDataKeys(): array
    {
        return ['alt_text', 'adobe_express_doc_id'];
    }

    protected function extractFileData(): array
    {
        $data = [];

        foreach ($this->fileDataKeys() as $key) {
            if ($this->has($key) && $this->input($key) !== null) {
                $data[$key] = $this->input($key);
            }
        }

        return $data;
    }
}
