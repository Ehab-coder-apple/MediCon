<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    protected $fillable = [
        'name',
        'contact_person',
        'phone',
        'email',
        'address',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get all purchases for this supplier
     */
    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    /**
     * Get total purchase amount for this supplier
     */
    public function getTotalPurchaseAmountAttribute(): float
    {
        return $this->purchases()->sum('total_cost');
    }

    /**
     * Get total number of purchases for this supplier
     */
    public function getTotalPurchasesAttribute(): int
    {
        return $this->purchases()->count();
    }

    /**
     * Get the last purchase date for this supplier
     */
    public function getLastPurchaseDateAttribute(): ?string
    {
        $lastPurchase = $this->purchases()->latest('purchase_date')->first();
        return $lastPurchase ? $lastPurchase->purchase_date->format('M d, Y') : null;
    }

    /**
     * Scope for active suppliers
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get formatted contact information
     */
    public function getContactInfoAttribute(): string
    {
        return "{$this->contact_person} - {$this->phone} - {$this->email}";
    }
}
