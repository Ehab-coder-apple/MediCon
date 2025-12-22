<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductInformation extends Model
{
    protected $table = 'product_information';

    protected $fillable = [
        'product_id',
        'active_ingredients',
        'side_effects',
        'indications',
        'dosage_information',
        'contraindications',
        'drug_interactions',
        'storage_requirements',
        'manufacturer_info',
        'regulatory_info',
        'source', // 'manual_entry', 'ai_extracted', 'external_api'
        'last_updated_by',
    ];

    protected $casts = [
        'active_ingredients' => 'array',
        'side_effects' => 'array',
        'indications' => 'array',
        'contraindications' => 'array',
        'drug_interactions' => 'array',
        'storage_requirements' => 'array',
        'manufacturer_info' => 'array',
        'regulatory_info' => 'array',
    ];

    /**
     * Get the product this information belongs to
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user who last updated this information
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_updated_by');
    }
}

