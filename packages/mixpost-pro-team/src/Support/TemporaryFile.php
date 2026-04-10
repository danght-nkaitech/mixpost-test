<?php

namespace Inovector\Mixpost\Support;

use Illuminate\Support\Facades\File as FileFacade;
use Inovector\Mixpost\Concerns\Makeable;
use LogicException;

final class TemporaryFile
{
    use Makeable;

    private TemporaryDirectory $directory;

    private string $filepath;

    public function __construct()
    {
        $this->directory = TemporaryDirectory::make();
    }

    public function fromUrl(string $url): self
    {
        $result = RemoteFileDownloader::make($url)
            ->temporaryDirectory($this->directory)
            ->validateAndDownload();

        $this->directory = $result->temporaryDirectory;
        $this->filepath = $result->filepath;

        return $this;
    }

    public function fromDisk(string $sourceDisk, string $sourceFilepath): self
    {
        $this->filepath = $this->directory->path($sourceFilepath);

        MediaFilesystem::copyFromDisk(
            sourceDisk: $sourceDisk,
            sourceFilepath: $sourceFilepath,
            destinationFilePath: $this->filepath
        );

        return $this;
    }

    public function filepath(): string
    {
        return $this->filepath;
    }

    public function contents(): string
    {
        $this->ensureFilePathExists();

        return FileFacade::get($this->filepath);
    }

    /**
     * @return bool|resource
     */
    public function readStream()
    {
        $this->ensureFilePathExists();

        return fopen($this->filepath, 'r');
    }

    public function delete(): bool
    {
        $this->ensureFilePathExists();

        return FileFacade::delete($this->filepath);
    }

    public function directory(): TemporaryDirectory
    {
        return $this->directory;
    }

    public function basename(): string
    {
        $this->ensureFilePathExists();

        return FileFacade::basename($this->filepath);
    }

    public function name(): string
    {
        $this->ensureFilePathExists();

        return FileFacade::name($this->filepath);
    }

    public function extension(): string
    {
        $this->ensureFilePathExists();

        return FileFacade::extension($this->filepath);
    }

    public function mimeType(): false|string
    {
        $this->ensureFilePathExists();

        return FileFacade::mimeType($this->filepath);
    }

    private function ensureFilePathExists(): void
    {
        if (! $this->filepath) {
            throw new LogicException('No file path set.');
        }
    }
}
