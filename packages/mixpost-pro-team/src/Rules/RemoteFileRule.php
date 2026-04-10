<?php

namespace Inovector\Mixpost\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Inovector\Mixpost\Exceptions\RemoteFileDownloadException;
use Inovector\Mixpost\Support\RemoteFileDownloader;

class RemoteFileRule implements ValidationRule
{
    protected int $timeout = 10;

    public function timeout(int $seconds): self
    {
        $this->timeout = $seconds;

        return $this;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        try {
            RemoteFileDownloader::make($value)
                ->connectTimeout($this->timeout)
                ->validate();
        } catch (RemoteFileDownloadException $e) {
            $fail($e->getMessage());
        }
    }
}
