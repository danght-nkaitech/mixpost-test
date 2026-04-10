<?php

namespace Inovector\MixpostEnterprise\Http\Base\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Inovector\MixpostEnterprise\Util;

class ReceiptResource extends JsonResource
{
    public static $wrap = null;

    public function toArray($request): array
    {
        return [
            'uuid' => $this->uuid,
            'workspace' => new WorkspaceResource($this->whenLoaded('workspace')),
            'transaction_id' => $this->transaction_id,
            'invoice_number' => $this->invoice_number,
            'amount' => $this->amount,
            'tax' => $this->tax,
            'currency' => $this->currency,
            'receipt_url' => $this->receipt_url,
            'description' => $this->description,
            'created_at' => Util::dateFormat($this->created_at),
            'paid_at' => Util::dateFormat($this->paid_at),
            'paid_at_raw' => $this->paid_at->format('Y-m-d H:i'),
        ];
    }
}
