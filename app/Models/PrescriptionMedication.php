<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PrescriptionMedication extends Model
{
    protected $table = 'prescription_medications';

    protected $fillable = [
        'prescription_check_id',
        'product_id',
        'medication_name',
        'dosage',
        'quantity_prescribed',
        'availability_status', // 'in_stock', 'out_of_stock', 'low_stock'
        'available_quantity',
        'confidence_score',
    ];

    protected $casts = [
        'quantity_prescribed' => 'integer',
        'available_quantity' => 'integer',
        'confidence_score' => 'integer',
    ];

    /**
     * Get the prescription check this medication belongs to
     */
    public function prescriptionCheck(): BelongsTo
    {
        return $this->belongsTo(PrescriptionCheck::class);
    }

    /**
     * Get the product this medication refers to
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get alternative products for this medication
     */
    public function alternatives(): HasMany
    {
        return $this->hasMany(AlternativeProduct::class, 'original_medication_id');
    }
}

