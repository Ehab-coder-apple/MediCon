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

    // Corporate chain: a single Central warehouse per HQ branch that feeds
    // stock down into each retail branch's Local warehouses.
    public const TYPE_HQ_MAIN = 'hq_main';

    public const SYSTEM_TYPES = [
        self::TYPE_MAIN,
        self::TYPE_ON_SHELF,
        self::TYPE_RECEIVED,
        self::TYPE_EXPIRED,
        self::TYPE_DAMAGED,
        self::TYPE_RETURNS,
    ];

    /**
     * Central warehouse types: restricted to HQ branches. Represents the
     * 'HQ Main Warehouse'.
     */
    public const CENTRAL_TYPES = [
        self::TYPE_HQ_MAIN,
    ];

    /**
     * Local warehouse types: restricted to RETAIL_PHARMACY branches.
     * TYPE_MAIN is the branch's 'Backroom Warehouse' and TYPE_ON_SHELF is
     * its 'Dispensing Shelf'.
     */
    public const LOCAL_TYPES = [
        self::TYPE_MAIN,
        self::TYPE_ON_SHELF,
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
            self::TYPE_HQ_MAIN => [
                'name' => 'HQ Main Warehouse',
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

    /**
     * Get or create the single Central (HQ Main Warehouse) for a given HQ
     * branch. Throws if the branch is not an HQ branch.
     */
    public static function getOrCreateHqWarehouse(int $tenantId, Branch $hqBranch): self
    {
        if (! $hqBranch->isHq()) {
            throw new \InvalidArgumentException('The HQ Main Warehouse can only be created for an HQ branch.');
        }

        return static::getOrCreateSystemWarehouse($tenantId, $hqBranch->id, self::TYPE_HQ_MAIN);
    }

    /**
     * Validate that a warehouse type is allowed for the given branch:
     *  - Central types (HQ Main Warehouse) require an HQ branch.
     *  - Local types (Backroom Warehouse / Dispensing Shelf) require a
     *    RETAIL_PHARMACY branch.
     * Warehouses with no branch (tenant-wide legacy warehouses) and other
     * uncategorized system types (received/expired/damaged/returns/custom)
     * are left unrestricted. Returns an error message on failure, or null
     * when the assignment is valid.
     */
    public static function assertBranchTypeAllowed(string $type, ?Branch $branch): ?string
    {
        if (! $branch) {
            return null;
        }

        if (in_array($type, self::CENTRAL_TYPES, true) && ! $branch->isHq()) {
            return 'This warehouse type is a Central (HQ) warehouse and can only be assigned to an HQ branch.';
        }

        if (in_array($type, self::LOCAL_TYPES, true) && ! $branch->isRetail()) {
            return 'This warehouse type is a Local (branch) warehouse and can only be assigned to a retail pharmacy branch.';
        }

        return null;
    }
}
