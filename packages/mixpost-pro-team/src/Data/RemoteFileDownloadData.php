<?php

namespace Inovector\Mixpost\Data;

use Inovector\Mixpost\Support\TemporaryDirectory;

final readonly class RemoteFileDownloadData
{
    public function __construct(
        public TemporaryDirectory $temporaryDirectory,
        public string $filepath,
        public string $filename,
    ) {}
}
