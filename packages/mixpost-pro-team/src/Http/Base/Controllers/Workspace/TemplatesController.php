<?php

namespace Inovector\Mixpost\Http\Base\Controllers\Workspace;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Inovector\Mixpost\Facades\AIManager;
use Inovector\Mixpost\Facades\ServiceManager;
use Inovector\Mixpost\Http\Base\Resources\TemplateResource;
use Inovector\Mixpost\Models\Template;

class TemplatesController extends Controller
{
    public function index(): Response
    {
        $templates = Template::latest()->latest('id')->get();

        return Inertia::render('Workspace/Templates/Index', [
            'templates' => fn () => TemplateResource::collection($templates)->resolve(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Workspace/Templates/CreateEdit', [
            'template' => null,
            'ai_is_ready_to_use' => AIManager::isReadyToUse(),
            'is_configured_service' => ServiceManager::isActive(),
        ]);
    }

    public function edit(Request $request): Response
    {
        $template = Template::firstOrFailByUuid($request->route('template'));

        return Inertia::render('Workspace/Templates/CreateEdit', [
            'template' => new TemplateResource($template),
            'ai_is_ready_to_use' => AIManager::isReadyToUse(),
            'is_configured_service' => ServiceManager::isActive(),
        ]);
    }
}
