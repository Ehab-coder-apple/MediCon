<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class AccessCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'tenant_id',
        'tenant_name',
        'status',
        'description',
        'max_uses',
        'used_count',
        'expires_at',
        'used_at',
        'used_by',
        'created_by',
        'access_type',
        'role_assignment',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
        'max_uses' => 'integer',
        'used_count' => 'integer',
    ];

    /**
     * Generate a unique access code
     */
    public static function generateUniqueCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (self::where('code', $code)->exists());

        return $code;
    }

    /**
     * Check if the access code is valid for use
     */
    public function isValid(): bool
    {
        return $this->status === 'active'
            && ($this->expires_at === null || $this->expires_at->isFuture())
            && ($this->max_uses === 0 || $this->used_count < $this->max_uses);
    }

    /**
     * Mark the access code as used
     */
    public function markAsUsed(User $user): bool
    {
        if (!$this->isValid()) {
            return false;
        }

        $this->increment('used_count');

        if ($this->used_count >= $this->max_uses && $this->max_uses > 0) {
            $this->update([
                'status' => 'used',
                'used_at' => now(),
                'used_by' => $user->id,
            ]);
        }

        return true;
    }

    /**
     * Get the tenant that owns the access code
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the user who created the access code
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who used the access code
     */
    public function usedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'used_by');
    }

    /**
     * Scope for active codes
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for expired codes
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now());
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'active' => 'green',
            'used' => 'blue',
            'expired' => 'yellow',
            'revoked' => 'red',
            default => 'gray'
        };
    }
}
