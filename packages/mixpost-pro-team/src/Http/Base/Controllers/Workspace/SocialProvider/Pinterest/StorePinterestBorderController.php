<?php

namespace Inovector\Mixpost\Http\Base\Controllers\Workspace\SocialProvider\Pinterest;

use Illuminate\Http\RedirectResponse;
use Inovector\Mixpost\Http\Base\Requests\Workspace\SocialProvider\Pinterest\StorePinterestBorder;

class StorePinterestBorderController
{
    public function __invoke(StorePinterestBorder $storePinterestBorder): RedirectResponse
    {
        $response = $storePinterestBorder->handle();

        if ($response->hasError()) {
            return redirect()->back()->with('error', $response->message);
        }

        return redirect()->back();
    }
}
