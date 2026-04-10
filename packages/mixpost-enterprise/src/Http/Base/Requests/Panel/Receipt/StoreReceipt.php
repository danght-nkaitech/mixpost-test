<?php

namespace Inovector\MixpostEnterprise\Http\Base\Requests\Panel\Receipt;

use Illuminate\Database\Eloquent\Model;
use Inovector\MixpostEnterprise\Models\Receipt;
use Inovector\MixpostEnterprise\Models\Workspace;
use Inovector\MixpostEnterprise\Rules\CurrencyRule;

class StoreReceipt extends ReceiptFormRequest
{
    public function rules(): array
    {
        return [
            'workspace_uuid' => ['required', 'string', 'max:255', 'exists:'.Workspace::class.',uuid'],
            'transaction_id' => ['required', 'string', 'max:255'],
            'invoice_number' => ['required', 'string', 'max:255', 'unique:'.Receipt::class],
            'amount' => ['required', 'numeric'],
            'tax' => ['required', 'numeric'],
            'currency' => ['required', new CurrencyRule],
            'receipt_url' => ['sometimes', 'nullable', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string', 'max:255'],
            'paid_at' => ['required', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'workspace_uuid.required' => __('mixpost-enterprise::workspace.workspace_required'),
            'invoice_number.unique' => __('mixpost-enterprise::finance.invoice_exists'),
            'paid_at.required' => __('mixpost-enterprise::finance.paid_date_time_required'),
        ];
    }

    public function handle(): Model
    {
        return Workspace::findByUuid($this->input('workspace_uuid'))->receipts()->create([
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
}
