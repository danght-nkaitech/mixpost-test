<?php

namespace Inovector\Mixpost\Http\Base\Controllers\Admin\Services\Mastodon;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Inovector\Mixpost\Http\Base\Requests\Admin\DeleteMastodonService;
use Inovector\Mixpost\Jobs\UnauthorizeMastodonServiceAccountsJob;
use Inovector\Mixpost\Models\Service;

class DeleteMastodonServiceController extends Controller
{
    public function __invoke(DeleteMastodonService $request): RedirectResponse
    {
        $serverName = $request->input('server');

        $serviceName = "mastodon.$serverName";

        Service::where('name', $serviceName)->delete();

        UnauthorizeMastodonServiceAccountsJob::dispatch($serverName);

        return redirect()->back();
    }
}
