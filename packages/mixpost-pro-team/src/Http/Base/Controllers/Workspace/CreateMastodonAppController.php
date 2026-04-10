<?php

namespace Inovector\Mixpost\Http\Base\Controllers\Workspace;

use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Inovector\Mixpost\Http\Base\Requests\Workspace\CreateMastodonApp;

class CreateMastodonAppController extends Controller
{
    public function __invoke(CreateMastodonApp $createMastodonApp): Response
    {
        $createMastodonApp->handle();

        return response()->noContent();
    }
}
