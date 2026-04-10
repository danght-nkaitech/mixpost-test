<?php

namespace Inovector\MixpostEnterprise\Http\Base\Requests\Panel\Receipt;

use Illuminate\Foundation\Http\FormRequest;
use Inovector\MixpostEnterprise\Models\Workspace;
use Inovector\MixpostEnterprise\Rules\CurrencyRule;

class ReceiptFormRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'workspace_uuid' => ['required', 'string', 'max:255', 'exists:'.Workspace::class.',uuid'],
            'transaction_id' => ['required', 'string', 'max:255'],
            'invoice_number' => ['required', 'string', 'max:255', 'unique:'.ReceiptFormRequest::class],
            'amount' => ['required', 'numeric'],
            'tax' => ['sometimes', 'nullable', 'numeric'],
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
}
