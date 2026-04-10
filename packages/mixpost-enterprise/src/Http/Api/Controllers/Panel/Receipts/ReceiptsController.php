<?php

namespace Inovector\MixpostEnterprise\Http\Api\Controllers\Panel\Receipts;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;
use Inovector\MixpostEnterprise\Builders\Receipt\ReceiptQuery;
use Inovector\MixpostEnterprise\Http\Api\Requests\Panel\Receipt\StoreReceipt;
use Inovector\MixpostEnterprise\Http\Api\Requests\Panel\Receipt\UpdateReceipt;
use Inovector\MixpostEnterprise\Http\Api\Resources\ReceiptResource;
use Inovector\MixpostEnterprise\Models\Receipt;

class ReceiptsController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $receipts = ReceiptQuery::apply($request)
            ->with('workspace')
            ->latest()
            ->paginate(20);

        return ReceiptResource::collection($receipts);
    }

    public function store(StoreReceipt $storeReceipt): ReceiptResource
    {
        $receipt = $storeReceipt->handle();

        return new ReceiptResource($receipt);
    }

    public function show(Request $request): ReceiptResource
    {
        $receipt = Receipt::firstOrFailByUuid($request->route('receipt'))->load('workspace');

        return new ReceiptResource($receipt);
    }

    public function update(UpdateReceipt $updateReceipt): JsonResponse
    {
        return response()->json([
            'success' => $updateReceipt->handle(),
        ]);
    }

    public function delete(Request $request): JsonResponse
    {
        return response()->json([
            'success' => Receipt::firstOrFailByUuid($request->route('receipt'))->delete(),
        ]);
    }
}
