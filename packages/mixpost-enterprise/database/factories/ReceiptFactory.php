<?php

namespace Inovector\MixpostEnterprise\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Inovector\MixpostEnterprise\Models\Receipt;
use Inovector\MixpostEnterprise\Models\Workspace;

class ReceiptFactory extends Factory
{
    protected $model = Receipt::class;

    public function definition()
    {
        return [
            'uuid' => $this->faker->uuid(),
            'workspace_id' => Workspace::factory(),
            'transaction_id' => Str::random(),
            'invoice_number' => 'INV-'.Str::random(),
            'amount' => $this->faker->randomFloat(2, 1, 1000),
            'tax' => 0,
            'currency' => 'USD',
            'quantity' => 1,
            'receipt_url' => $this->faker->url(),
            'description' => $this->faker->sentence(),
            'paid_at' => $this->faker->dateTime(),
        ];
    }
}
