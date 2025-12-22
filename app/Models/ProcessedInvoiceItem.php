<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcessedInvoiceItem extends Model
{
    protected $table = 'processed_invoice_items';

    protected $fillable = [
        'processed_invoice_id',
        'product_id',
        'product_name',
        'product_code',
        'quantity',
        'unit_price',
        'total_price',
        'batch_number',
        'expiry_date',
        'manufacturer',
        'confidence_score', // OCR confidence (0-100)
        'manual_correction', // Flag if item was manually corrected
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'expiry_date' => 'date',
        'confidence_score' => 'integer',
        'manual_correction' => 'boolean',
    ];

    /**
     * Get the processed invoice this item belongs to
     */
    public function processedInvoice(): BelongsTo
    {
        return $this->belongsTo(ProcessedInvoice::class);
    }

    /**
     * Get the product this item refers to
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}

