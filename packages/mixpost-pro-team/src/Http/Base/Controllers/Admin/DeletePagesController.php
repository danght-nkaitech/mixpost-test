<?php

namespace Inovector\Mixpost\Http\Base\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inovector\Mixpost\Models\Page;

class DeletePagesController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        Page::whereIn('uuid', $request->input('pages', []))->delete();

        return redirect()->back();
    }
}
