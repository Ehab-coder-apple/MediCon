<?php

namespace App\Traits;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToTenant
{
    /**
     * Boot the trait
     */
    protected static function bootBelongsToTenant(): void
    {
        // Temporarily disable global scoping to prevent memory issues
        // TODO: Implement proper tenant resolution middleware

        // Automatically set tenant_id when creating
        static::creating(function ($model) {
            if (empty($model->tenant_id)) {
                // Try to get current tenant from container, but don't fail if not set
                try {
                    $currentTenant = app('current_tenant');
                    if ($currentTenant) {
                        $model->tenant_id = $currentTenant->id;
                    }
                } catch (\Exception $e) {
                    // current_tenant not set in container, tenant_id must be set explicitly
                }
            }
        });

        // Global scope disabled temporarily
        // static::addGlobalScope('tenant', function (Builder $builder) {
        //     $currentTenant = app('current_tenant');
        //     if ($currentTenant) {
        //         $builder->where($builder->getModel()->getTable() . '.tenant_id', $currentTenant->id);
        //     }
        // });
    }

    /**
     * Get the tenant that owns the model
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Scope query to specific tenant
     */
    public function scopeForTenant(Builder $query, Tenant $tenant): Builder
    {
        return $query->where('tenant_id', $tenant->id);
    }

    /**
     * Scope query to current tenant
     */
    public function scopeForCurrentTenant(Builder $query): Builder
    {
        $currentTenant = app('current_tenant');
        if ($currentTenant) {
            return $query->where('tenant_id', $currentTenant->id);
        }
        return $query;
    }

    /**
     * Check if model belongs to current tenant
     */
    public function belongsToCurrentTenant(): bool
    {
        $currentTenant = app('current_tenant');
        return $currentTenant && $this->tenant_id === $currentTenant->id;
    }

    /**
     * Get the tenant ID column name
     */
    public function getTenantIdColumn(): string
    {
        return 'tenant_id';
    }
}
