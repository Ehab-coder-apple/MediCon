<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'domain',
        'database_name',
        'pharmacy_name',
        'pharmacy_license',
        'contact_email',
        'contact_phone',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'subscription_plan',
        'subscription_status',
        'subscription_expires_at',
        'subscription_amount',
        'max_users',
        'is_active',
        'settings',
        'setup_completed',
        'license_number',
    ];

    protected $casts = [
        'subscription_expires_at' => 'datetime',
        'is_active' => 'boolean',
        'setup_completed' => 'boolean',
        'settings' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get all users for this tenant
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get all products for this tenant
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get all customers for this tenant
     */
    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    /**
     * Get all sales for this tenant
     */
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    /**
     * Get all suppliers for this tenant
     */
    public function suppliers(): HasMany
    {
        return $this->hasMany(Supplier::class);
    }

    /**
     * Get all roles for this tenant
     */
    public function roles(): HasMany
    {
        return $this->hasMany(Role::class);
    }

    /**
     * Check if tenant subscription is active
     */
    public function isSubscriptionActive(): bool
    {
        return $this->is_active &&
               $this->subscription_status === 'active' &&
               ($this->subscription_expires_at === null || $this->subscription_expires_at->isFuture());
    }

    /**
     * Check if tenant can add more users
     */
    public function canAddUsers(): bool
    {
        return $this->users()->count() < $this->max_users;
    }

    /**
     * Get tenant by domain
     */
    public static function findByDomain(string $domain): ?self
    {
        return static::where('domain', $domain)
                    ->where('is_active', true)
                    ->first();
    }

    /**
     * Get tenant by slug
     */
    public static function findBySlug(string $slug): ?self
    {
        return static::where('slug', $slug)
                    ->where('is_active', true)
                    ->first();
    }
}
