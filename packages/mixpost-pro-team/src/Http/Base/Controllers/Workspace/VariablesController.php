<?php

namespace Inovector\Mixpost\Http\Base\Controllers\Workspace;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;
use Inovector\Mixpost\Http\Base\Requests\Workspace\StoreVariable;
use Inovector\Mixpost\Http\Base\Requests\Workspace\UpdateVariable;
use Inovector\Mixpost\Http\Base\Resources\VariableResource;
use Inovector\Mixpost\Models\Variable;

class VariablesController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $records = Variable::latest()->latest('id')->get();

        return VariableResource::collection($records);
    }

    public function store(StoreVariable $storeVariable): JsonResponse
    {
        $record = $storeVariable->handle();

        return response()->json($record);
    }

    public function update(UpdateVariable $updateVariable): JsonResponse
    {
        $record = $updateVariable->handle();

        return response()->json($record);
    }

    public function destroy(Request $request): JsonResponse
    {
        $deleteRecord = Variable::where('uuid', $request->route('variable'))->delete();

        return response()->json($deleteRecord);
    }
}
