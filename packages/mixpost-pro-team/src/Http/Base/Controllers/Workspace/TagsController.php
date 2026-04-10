<?php

namespace Inovector\Mixpost\Http\Base\Controllers\Workspace;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inovector\Mixpost\Http\Base\Requests\Workspace\StoreTag;
use Inovector\Mixpost\Http\Base\Requests\Workspace\UpdateTag;
use Inovector\Mixpost\Models\Tag;

class TagsController extends Controller
{
    public function store(StoreTag $storeTag): RedirectResponse
    {
        $storeTag->handle();

        return redirect()->back();
    }

    public function update(UpdateTag $updateTag): RedirectResponse
    {
        $updateTag->handle();

        return redirect()->back();
    }

    public function destroy(Request $request): RedirectResponse
    {
        Tag::where('uuid', $request->route('tag'))->delete();

        return redirect()->back();
    }
}
