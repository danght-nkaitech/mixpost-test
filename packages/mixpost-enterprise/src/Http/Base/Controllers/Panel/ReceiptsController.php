<?php

namespace Inovector\MixpostEnterprise\Http\Base\Controllers\Panel;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Inovector\MixpostEnterprise\Builders\Receipt\ReceiptQuery;
use Inovector\MixpostEnterprise\Configs\BillingConfig;
use Inovector\MixpostEnterprise\Http\Base\Requests\Panel\Receipt\StoreReceipt;
use Inovector\MixpostEnterprise\Http\Base\Requests\Panel\Receipt\UpdateReceipt;
use Inovector\MixpostEnterprise\Http\Base\Resources\ReceiptResource;
use Inovector\MixpostEnterprise\Models\Receipt;
use Inovector\MixpostEnterprise\Util;

class ReceiptsController extends Controller
{
    public function index(Request $request): Response
    {
        $receipts = ReceiptQuery::apply($request)
            ->with('workspace')
            ->latest()
            ->paginate(20)
            ->onEachSide(1)
            ->withQueryString();

        return Inertia::render('Panel/Receipts/Index', [
            'receipts' => fn () => ReceiptResource::collection($receipts),
            'filter' => [
                'invoice_number' => $request->query('invoice_number', ''),
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Panel/Receipts/CreateEdit', [
            'mode' => 'create',
            'defaultCurrency' => (new BillingConfig)->get('currency'),
            'currencies' => Util::currenciesForSelect(),
        ]);
    }

    public function store(StoreReceipt $storeReceipt): RedirectResponse
    {
        $receipt = $storeReceipt->handle();

        return redirect()->route('mixpost_e.receipts.view', ['receipt' => $receipt->uuid])->with('success', __('mixpost-enterprise::finance.receipt_created'));
    }

    public function view(Request $request): Response
    {
        $receipt = Receipt::firstOrFailByUuid($request->route('receipt'))->load('workspace');

        return Inertia::render('Panel/Receipts/View', [
            'receipt' => new ReceiptResource($receipt),
        ]);
    }

    public function edit(Request $request): Response
    {
        $receipt = Receipt::firstOrFailByUuid($request->route('receipt'))->load('workspace');

        return Inertia::render('Panel/Receipts/CreateEdit', [
            'mode' => 'edit',
            'defaultCurrency' => (new BillingConfig)->get('currency'),
            'currencies' => Util::currenciesForSelect(),
            'receipt' => new ReceiptResource($receipt),
        ]);
    }

    public function update(UpdateReceipt $updateReceipt): RedirectResponse
    {
        $updateReceipt->handle();

        return redirect()->back()->with('success', __('mixpost-enterprise::finance.receipt_updated'));
    }

    public function delete(Request $request): RedirectResponse
    {
        Receipt::where('uuid', $request->route('receipt'))->delete();

        return redirect()->route('mixpost_e.receipts.index')->with('success', 'mixpost::finance.receipt_deleted');
    }
}
