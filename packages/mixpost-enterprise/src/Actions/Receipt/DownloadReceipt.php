<?php

namespace Inovector\MixpostEnterprise\Actions\Receipt;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Inovector\MixpostEnterprise\Configs\BillingConfig;
use Inovector\MixpostEnterprise\Facades\Theme;
use Inovector\MixpostEnterprise\Models\Receipt;

class DownloadReceipt
{
    public function __invoke(Receipt $receipt): Response
    {
        $config = new BillingConfig;

        $ownerName = $receipt->workspace->owner->name ?? '';

        $data = [
            'receipt' => $receipt->toArray(),
            'config' => [
                'receipt_title' => $config->get('receipt_title') ?: __('mixpost-enterprise::general.receipt'),
                'company_details' => $config->get('company_details'),
            ],
            'to' => $ownerName,
            'color' => [
                'primary' => Arr::get(Theme::configuredColors(), 'primary_colors.500', '#eee'),
                'accent' => Arr::get(Theme::configuredColors(), 'primary_colors.700', '#ddd'),
                'primary_context' => Arr::get(Theme::configuredColors(), 'primary_context', '#000'),
            ],
        ];

        $pdf = Pdf::loadView('mixpost-enterprise::pdf.receipt', $data);

        return $pdf->download($receipt->invoice_number.'.pdf');
    }
}
