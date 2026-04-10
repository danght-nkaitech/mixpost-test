<?php

namespace Inovector\Mixpost\Http\Base\Requests\Workspace\Media;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Inovector\Mixpost\Abstracts\Image;
use Inovector\Mixpost\Concerns\UsesMediaFileDataRules;
use Inovector\Mixpost\Concerns\UsesMediaPath;
use Inovector\Mixpost\Events\Media\UploadingMediaFile;
use Inovector\Mixpost\Exceptions\ChunkedUploadSessionNotFound;
use Inovector\Mixpost\MediaConversions\MediaImageResizerConversion;
use Inovector\Mixpost\MediaConversions\MediaVideoThumbConversion;
use Inovector\Mixpost\Models\Media;
use Inovector\Mixpost\Support\ChunkedUpload;
use Inovector\Mixpost\Support\MediaUploader;

class ChunkedUploadComplete extends FormRequest
{
    use UsesMediaFileDataRules;
    use UsesMediaPath;

    public function rules(): array
    {
        return $this->mediaFileDataRules();
    }

    public function handle(): Media
    {
        $chunkedUpload = new ChunkedUpload;
        $uploadUuid = $this->route('uploadUuid');

        try {
            $file = $chunkedUpload->complete($uploadUuid);
        } catch (ChunkedUploadSessionNotFound $e) {
            throw ValidationException::withMessages([
                'upload_session' => ['Upload session not found.'],
            ]);
        }

        UploadingMediaFile::dispatch($file);

        $media = MediaUploader::fromSource($file)
            ->path(self::mediaWorkspacePathWithDateSubpath())
            ->conversions([
                MediaImageResizerConversion::name('thumb')->width(Image::MEDIUM_WIDTH)->height(Image::MEDIUM_HEIGHT),
                MediaVideoThumbConversion::name('thumb')->atSecond(5),
            ])
            ->data($this->extractFileData())
            ->uploadAndInsert();

        $chunkedUpload->cleanup($uploadUuid);

        return $media;
    }
}
