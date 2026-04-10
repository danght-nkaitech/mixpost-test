<?php

namespace Inovector\Mixpost\Actions\Account;

use Exception;
use Illuminate\Support\Arr;
use Inovector\Mixpost\Abstracts\Image;
use Inovector\Mixpost\Concerns\UsesMediaPath;
use Inovector\Mixpost\Events\Account\AccountAdded;
use Inovector\Mixpost\Events\Account\AccountUpdated;
use Inovector\Mixpost\Mixpost;
use Inovector\Mixpost\Models\Account;
use Inovector\Mixpost\Support\AccountSuffix;
use Inovector\Mixpost\Support\ImageResizer;
use Inovector\Mixpost\Support\TemporaryFile;

class UpdateOrCreateAccount
{
    use UsesMediaPath;

    public function __invoke(string $providerName, array $account, array $accessToken): void
    {
        $params = [
            'name' => $account['name'],
            'username' => $account['username'] ?? null,
            'media' => $this->downloadAvatar($account['image']),
            'data' => $account['data'] ?? null,
            'access_token' => $accessToken,
        ];

        $record = Account::where('provider', $providerName)->where('provider_id', $account['id'])->first();

        if (! $record) {
            $account = Account::create(array_merge(
                [
                    'provider' => $providerName,
                    'provider_id' => $account['id'],
                    'authorized' => true,
                ],
                $params
            ));

            AccountAdded::dispatch($account);

            return;
        }

        // If the suffix has been edited, we keep it.
        $getRecordDataSuffix = Arr::get($record->data, AccountSuffix::key());

        if ($getRecordDataSuffix && AccountSuffix::isEdited($record->data)) {
            $params['data']['suffix'] = $getRecordDataSuffix;
        }

        $record->update(array_merge(
            [
                'authorized' => true,
            ],
            $params
        ));

        AccountUpdated::dispatch($record);
    }

    protected function downloadAvatar(?string $imageUrl): ?array
    {
        if (! $imageUrl) {
            return null;
        }

        $temporaryFile = null;

        try {
            $temporaryFile = TemporaryFile::make()->fromUrl($imageUrl);

            $image = ImageResizer::make($temporaryFile->filepath())
                ->path(self::mediaWorkspacePathWithAvatarsSubpath());

            $image->resize(Image::THUMBNAIL_WIDTH, Image::THUMBNAIL_HEIGHT);

            return [
                'disk' => $image->getDisk(),
                'path' => $image->getDestinationFilePath(),
            ];
        } catch (Exception $exception) {
            Mixpost::reportException($exception);

            return null;
        } finally {
            $temporaryFile?->directory()->delete();
        }
    }
}
