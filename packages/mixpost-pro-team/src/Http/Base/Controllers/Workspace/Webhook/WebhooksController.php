<?php

namespace Inovector\Mixpost\Http\Base\Controllers\Workspace\Webhook;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Inovector\Mixpost\Builders\Webhook\WebhookQuery;
use Inovector\Mixpost\Facades\WorkspaceManager;
use Inovector\Mixpost\Http\Base\Requests\Workspace\StoreWebhook;
use Inovector\Mixpost\Http\Base\Requests\Workspace\UpdateWebhook;
use Inovector\Mixpost\Http\Base\Resources\WebhookResource;
use Inovector\Mixpost\Models\Webhook;
use Inovector\Mixpost\WebhookManager as MixpostWebhook;

class WebhooksController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection|Response
    {
        $records = WebhookQuery::apply($request)
            ->byWorkspace(WorkspaceManager::current())
            ->latest()
            ->latest('id')
            ->paginate(20)
            ->onEachSide(1)
            ->withQueryString();

        return Inertia::render('Workspace/Webhooks/Index', [
            'filter' => [
                'keyword' => $request->query('keyword', ''),
            ],
            'records' => fn () => WebhookResource::collection($records),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Workspace/Webhooks/CreateEdit', [
            'mode' => 'create',
            'record' => null,
            'events' => MixpostWebhook::getWorkspaceEventSelectionOptions(),
        ]);
    }

    public function store(StoreWebhook $storeWebhook): RedirectResponse
    {
        $record = $storeWebhook->handle();

        return redirect()
            ->route('mixpost.webhooks.edit', ['workspace' => WorkspaceManager::current()->uuid, 'webhook' => $record->uuid])
            ->with('success', __('mixpost::webhook.created'));
    }

    public function edit(Request $request): Response
    {
        $record = Webhook::firstOrFailByUuid($request->route('webhook'));

        if (! $record->isForWorkspace(WorkspaceManager::current())) {
            abort(404);
        }

        return Inertia::render('Workspace/Webhooks/CreateEdit', [
            'mode' => 'edit',
            'record' => new WebhookResource($record),
            'events' => MixpostWebhook::getWorkspaceEventSelectionOptions(),
        ]);
    }

    public function update(UpdateWebhook $updateWebhook): RedirectResponse
    {
        $updateWebhook->handle();

        return redirect()->back()->with('success', __('mixpost::webhook.updated'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $query = Webhook::byWorkspace(WorkspaceManager::current())->where('uuid', $request->route('webhook'))->delete();

        if (! $query) {
            return redirect()
                ->route('mixpost.webhooks.index', ['workspace' => $request->route('workspace')])
                ->with('error', __('mixpost::webhook.not_found'));
        }

        return redirect()->route('mixpost.webhooks.index', ['workspace' => $request->route('workspace')])
            ->with('success', __('mixpost::webhook.deleted'));
    }
}
