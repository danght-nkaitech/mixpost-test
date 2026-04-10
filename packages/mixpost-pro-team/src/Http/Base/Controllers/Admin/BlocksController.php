<?php

namespace Inovector\Mixpost\Http\Base\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inovector\Mixpost\Concerns\UsesUserModel;
use Inovector\Mixpost\Http\Base\Requests\Admin\StoreBlock;
use Inovector\Mixpost\Http\Base\Requests\Admin\UpdateBlock;
use Inovector\Mixpost\Models\Block;

class BlocksController extends Controller
{
    use UsesUserModel;

    public function store(StoreBlock $storeBlock): RedirectResponse
    {
        $storeBlock->handle();

        return redirect()->back();
    }

    public function update(UpdateBlock $updateBlock): RedirectResponse
    {
        $updateBlock->handle();

        return redirect()->back();
    }

    public function delete(Request $request): RedirectResponse
    {
        $block = Block::where('id', $request->route('block'))->firstOrFail();

        $block->delete();

        return redirect()->back();
    }
}
