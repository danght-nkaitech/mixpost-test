<?php

namespace Inovector\Mixpost\Http\Base\Controllers\Admin\Services\Mastodon;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Inovector\Mixpost\Actions\Common\CreateMastodonApp;
use Inovector\Mixpost\Http\Base\Requests\Admin\RecreateMastodonService;
use Inovector\Mixpost\Jobs\UnauthorizeMastodonServiceAccountsJob;

class RecreateMastodonServiceController extends Controller
{
    public function __invoke(RecreateMastodonService $request): JsonResponse
    {
        $serverName = $request->input('server');

        $result = (new CreateMastodonApp)($serverName);

        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], 422);
        }

        UnauthorizeMastodonServiceAccountsJob::dispatch($serverName);

        return response()->json(['success' => true]);
    }
}
