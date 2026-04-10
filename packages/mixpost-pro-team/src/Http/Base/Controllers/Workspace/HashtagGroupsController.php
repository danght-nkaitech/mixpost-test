<?php

namespace Inovector\Mixpost\Http\Base\Controllers\Workspace;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;
use Inovector\Mixpost\Http\Base\Requests\Workspace\StoreHashtagGroup;
use Inovector\Mixpost\Http\Base\Requests\Workspace\UpdateHashtagGroup;
use Inovector\Mixpost\Http\Base\Resources\HashtagGroupResource;
use Inovector\Mixpost\Models\HashtagGroup;

class HashtagGroupsController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $records = HashtagGroup::latest()->get();

        return HashtagGroupResource::collection($records);
    }

    public function store(StoreHashtagGroup $storeHashtagGroup): JsonResponse
    {
        $record = $storeHashtagGroup->handle();

        return response()->json($record);
    }

    public function update(UpdateHashtagGroup $updateHashtagGroup): JsonResponse
    {
        $record = $updateHashtagGroup->handle();

        return response()->json($record);
    }

    public function destroy(Request $request): JsonResponse
    {
        $deleteRecord = HashtagGroup::where('uuid', $request->route('hashtaggroup'))->delete();

        return response()->json($deleteRecord);
    }
}
