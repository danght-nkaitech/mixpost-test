<?php

namespace Inovector\Mixpost\Support;

use FilesystemIterator;
use Inovector\Mixpost\Util;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Spatie\TemporaryDirectory\TemporaryDirectory as BaseTemporaryDirectory;

class TemporaryDirectory extends BaseTemporaryDirectory
{
    public function __construct(string $location = '')
    {
        $this->name = bin2hex(random_bytes(16));

        parent::__construct($this->sanitizePath($location ?: static::getParentTemporaryDirectoryPath()));
    }

    public static function make(string $location = ''): self
    {
        return (new self($location))->create();
    }

    public static function getParentTemporaryDirectoryPath(): string
    {
        return Util::config('temporary_directory_path') ?? storage_path('mixpost-media/temp');
    }

    public function getSize(): int
    {
        if (! is_dir($this->location)) {
            return 0;
        }

        $size = 0;
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->location, FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $size += $file->getSize();
            }
        }

        return $size;
    }
}
