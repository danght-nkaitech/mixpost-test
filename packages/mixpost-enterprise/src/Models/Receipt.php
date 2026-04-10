<?php

namespace Inovector\MixpostEnterprise\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\RedirectResponse;
use Inovector\Mixpost\Concerns\Model\HasUuid;
use Inovector\MixpostEnterprise\Concerns\PaymentPlatform;

class Receipt extends Model
{
    use HasFactory;
    use HasUuid;
    use PaymentPlatform;

    public $table = 'mixpost_e_receipts';

    protected $fillable = [
        'transaction_id',
        'invoice_number',
        'platform_plan_id',
        'amount',
        'tax',
        'currency',
        'quantity',
        'receipt_url',
        'description',
        'paid_at',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'paid_at' => 'datetime',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class, 'workspace_id', 'id');
    }

    public function hasUrl(): bool
    {
        return $this->receipt_url != null;
    }

    public function supportReceiptUrl(): bool
    {
        return $this->activePlatform()->supportReceiptUrl();
    }

    public function redirectToPdf(): RedirectResponse
    {
        return redirect($this->activePlatform()->getReceiptUrl($this->transaction_id));
    }
}
