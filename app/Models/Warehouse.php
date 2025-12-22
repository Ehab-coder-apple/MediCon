<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warehouse extends Model
{
    use BelongsToTenant;

    /**
     * Predefined warehouse types
     */
    public const TYPE_MAIN = 'main';
    public const TYPE_ON_SHELF = 'on_shelf';
    public const TYPE_RECEIVED = 'received';
    public const TYPE_EXPIRED = 'expired';
    public const TYPE_DAMAGED = 'damaged';
    public const TYPE_RETURNS = 'returns';
    public const TYPE_CUSTOM = 'custom';

    public const SYSTEM_TYPES = [
        self::TYPE_MAIN,
        self::TYPE_ON_SHELF,
        self::TYPE_RECEIVED,
        self::TYPE_EXPIRED,
        self::TYPE_DAMAGED,
        self::TYPE_RETURNS,
    ];

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'name',
        'type',
        'is_sellable',
        'is_system',
        'specifications',
    ];

    protected $casts = [
        'is_sellable' => 'boolean',
        'is_system' => 'boolean',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(WarehouseStock::class);
    }

    /**
     * Scope: only sellable warehouses (e.g. On Shelf)
     */
    public function scopeSellable(Builder $query): Builder
    {
        return $query->where('is_sellable', true);
    }

    /**
     * Scope: filter by tenant and optionally branch (including global warehouses when branch is set)
     */
    public function scopeForTenantAndBranch(Builder $query, int $tenantId, ?int $branchId = null): Builder
    {
        $query->where('tenant_id', $tenantId);

        if (! is_null($branchId)) {
            $query->where(function (Builder $q) use ($branchId) {
                $q->whereNull('branch_id')
                    ->orWhere('branch_id', $branchId);
            });
        }

        return $query;
    }

    /**
     * Get configuration (name, is_sellable) for a system warehouse type.
     */
    public static function getSystemTypeConfig(string $type): array
    {
        $map = [
            self::TYPE_MAIN => [
                'name' => 'Main Warehouse',
                'is_sellable' => false,
            ],
            self::TYPE_ON_SHELF => [
                'name' => 'On Shelf',
                'is_sellable' => true,
            ],
            self::TYPE_RECEIVED => [
                'name' => 'Received Goods',
                'is_sellable' => false,
            ],
            self::TYPE_EXPIRED => [
                'name' => 'Expired Goods',
                'is_sellable' => false,
            ],
            self::TYPE_DAMAGED => [
                'name' => 'Damaged Goods',
                'is_sellable' => false,
            ],
            self::TYPE_RETURNS => [
                'name' => 'Returns Warehouse',
                'is_sellable' => false,
            ],
        ];

        return $map[$type] ?? [
            'name' => ucfirst(str_replace('_', ' ', $type)),
            'is_sellable' => false,
        ];
    }

    /**
     * Get or create a system warehouse for a given tenant/branch and type.
     */
    public static function getOrCreateSystemWarehouse(int $tenantId, ?int $branchId, string $type): self
    {
        $config = static::getSystemTypeConfig($type);

        return static::firstOrCreate(
            [
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'type' => $type,
                'is_system' => true,
            ],
            [
                'name' => $config['name'],
                'is_sellable' => $config['is_sellable'],
            ]
        );
    }

    /**
     * Ensure all default system warehouses exist for a tenant/branch.
     */
    public static function ensureDefaultSystemWarehouses(int $tenantId, ?int $branchId = null): void
    {
        foreach (self::SYSTEM_TYPES as $type) {
            static::getOrCreateSystemWarehouse($tenantId, $branchId, $type);
        }
    }
}
