<?php

namespace Inovector\Mixpost\Http\Base\Controllers\Public;

use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;
use Inovector\Mixpost\Models\Page;

class PagesController extends Controller
{
    public function __invoke(?string $slug = null): View
    {
        $page = Page::where('status', true)->where('slug', $slug ?: 'home')->firstOrFail();

        return view('mixpost::public.page', [
            'page' => $page,
        ]);
    }
}
