<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Customer extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'email',
        'tenant_id',
    ];

    /**
     * Get all sales for this customer
     */
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }



    /**
     * Get all invoices for this customer
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }



    /**
     * Get the tenant that owns this customer
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get total purchase amount for this customer
     */
    public function getTotalPurchaseAmountAttribute(): float
    {
        return $this->sales()->sum('total_price');
    }

    /**
     * Get total number of purchases for this customer
     */
    public function getTotalPurchasesAttribute(): int
    {
        return $this->sales()->count();
    }

    /**
     * Get the last purchase date for this customer
     */
    public function getLastPurchaseDateAttribute(): ?string
    {
        $lastSale = $this->sales()->latest('sale_date')->first();
        return $lastSale ? $lastSale->sale_date->format('M d, Y') : null;
    }

    /**
     * Get formatted contact information
     */
    public function getContactInfoAttribute(): string
    {
        $contact = [];
        if ($this->phone) $contact[] = $this->phone;
        if ($this->email) $contact[] = $this->email;
        return implode(' | ', $contact);
    }


}
