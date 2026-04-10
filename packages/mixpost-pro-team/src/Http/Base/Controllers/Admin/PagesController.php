<?php

namespace Inovector\Mixpost\Http\Base\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Inovector\Mixpost\Http\Base\Requests\Admin\StorePage;
use Inovector\Mixpost\Http\Base\Requests\Admin\UpdatePage;
use Inovector\Mixpost\Http\Base\Resources\BlockResource;
use Inovector\Mixpost\Http\Base\Resources\PageResource;
use Inovector\Mixpost\Models\Block;
use Inovector\Mixpost\Models\Page;

class PagesController extends Controller
{
    public function index(): Response
    {
        $pages = Page::latest()->get();

        return Inertia::render('Admin/Pages/Pages', [
            'pages' => PageResource::collection($pages),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Pages/CreateEdit', [
            'mode' => 'create',
            'page' => null,
            'blocks' => BlockResource::collection(Block::latest()->get()),
        ]);
    }

    public function store(StorePage $storePage): RedirectResponse
    {
        $page = $storePage->handle();

        return redirect()->route('mixpost.pages.edit', ['page' => $page->uuid]);
    }

    public function edit(Request $request): Response
    {
        $page = Page::firstOrFailByUuid($request->route('page'));

        $page->load('blocks');

        return Inertia::render('Admin/Pages/CreateEdit', [
            'mode' => 'edit',
            'page' => new PageResource($page),
            'blocks' => BlockResource::collection(Block::latest()->get()),
        ]);
    }

    public function update(UpdatePage $updatePage): RedirectResponse
    {
        $updatePage->handle();

        return redirect()->back();
    }

    public function delete(Request $request): RedirectResponse
    {
        $page = Page::firstOrFailByUuid($request->route('page'));

        $page->delete();

        return redirect()->route('mixpost.pages.index');
    }
}
