<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlternativeProduct extends Model
{
    protected $table = 'alternative_products';

    protected $fillable = [
        'prescription_medication_id',
        'original_medication_id',
        'alternative_product_id',
        'branch_id',
        'similarity_score', // 0-100, how similar is the alternative
        'reason', // 'same_active_ingredient', 'same_category', 'same_therapeutic_use'
        'available_quantity',
        'shelf_location',
        'price_difference',
    ];

    protected $casts = [
        'similarity_score' => 'integer',
        'available_quantity' => 'integer',
        'price_difference' => 'decimal:2',
    ];

    /**
     * Get the prescription medication this alternative is for
     */
    public function prescriptionMedication(): BelongsTo
    {
        return $this->belongsTo(PrescriptionMedication::class);
    }

    /**
     * Get the original medication
     */
    public function originalMedication(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'original_medication_id');
    }

    /**
     * Get the alternative product
     */
    public function alternativeProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'alternative_product_id');
    }

    /**
     * Get the branch this alternative is available in
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}

