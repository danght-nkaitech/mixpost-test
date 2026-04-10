<?php

namespace Inovector\MixpostEnterprise\Http\Base\Requests\Panel\Receipt;

use Inovector\MixpostEnterprise\Models\Receipt;

class UpdateReceipt extends ReceiptFormRequest
{
    private ?Receipt $receipt = null;

    public function rules(): array
    {
        $rules = parent::rules();

        $rules['workspace_uuid'] = ['sometimes', 'nullable'];
        $rules['invoice_number'] = ['required', 'string', 'max:255', 'unique:'.Receipt::class.',invoice_number,'.$this->receipt()->id];

        return $rules;
    }

    public function handle(): bool
    {
        return $this->receipt()->update([
            'transaction_id' => $this->input('transaction_id'),
            'invoice_number' => $this->input('invoice_number'),
            'amount' => $this->input('amount', 0),
            'tax' => $this->input('tax', 0),
            'currency' => $this->input('currency'),
            'quantity' => 1,
            'receipt_url' => $this->input('receipt_url'),
            'description' => $this->input('description'),
            'paid_at' => $this->input('paid_at'),
        ]);
    }

    private function receipt(): Receipt
    {
        if ($this->receipt) {
            return $this->receipt;
        }

        return $this->receipt = Receipt::firstOrFailByUuid($this->route('receipt'));
    }
}
