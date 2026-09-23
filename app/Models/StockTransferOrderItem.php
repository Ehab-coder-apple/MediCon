<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockTransferOrderItem extends Model
{
    protected $fillable = [
        'stock_transfer_order_id',
        'product_id',
        'batch_id',
        'quantity',
        'currency_unit_cost',
    ];

    protected $casts = [
        // Isolated audit value (see migration comment): the cost of this
        // item at the moment it was transferred, kept separate from the
        // live product/batch cost_price for future payroll bonus/waste
        // deduction calculations.
        'currency_unit_cost' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(StockTransferOrder::class, 'stock_transfer_order_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }
}
