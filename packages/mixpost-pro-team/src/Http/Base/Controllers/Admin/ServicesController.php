<?php

namespace Inovector\Mixpost\Http\Base\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Inovector\Mixpost\Facades\ServiceManager;
use Inovector\Mixpost\Http\Base\Requests\Admin\SaveService;
use Inovector\Mixpost\Models\Service;

class ServicesController extends Controller
{
    public function index(): Response
    {
        $mastodonServers = Service::where('name', 'like', 'mastodon.%')
            ->select('id', 'name')
            ->get()
            ->map(function (Service $service) {
                return [
                    'id' => $service->id,
                    'server' => Str::after($service->name, 'mastodon.'),
                ];
            })
            ->values();

        return Inertia::render('Admin/Configuration/Services', [
            'services' => ServiceManager::all(),
            'mastodonServers' => $mastodonServers,
        ]);
    }

    public function update(SaveService $saveService): RedirectResponse
    {
        $saveService->handle();

        return back();
    }
}
