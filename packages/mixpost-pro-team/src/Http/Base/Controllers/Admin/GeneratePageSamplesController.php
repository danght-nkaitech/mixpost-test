<?php

namespace Inovector\Mixpost\Http\Base\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Inovector\Mixpost\Http\Base\Requests\Admin\GeneratePageSamples;

class GeneratePageSamplesController extends Controller
{
    public function __invoke(GeneratePageSamples $generatePageSamples): RedirectResponse
    {
        $generatePageSamples->handle();

        return redirect()->back();
    }
}
