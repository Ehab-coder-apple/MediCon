<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsAppCredential extends Model
{
    protected $table = 'whatsapp_credentials';

    protected $fillable = [
        'tenant_id',
        'business_account_id',
        'phone_number_id',
        'phone_number',
        'access_token',
        'webhook_secret',
        'is_enabled',
        'is_verified',
        'verification_code',
        'verified_at',
        'last_tested_at',
        'test_result',
        // Dual-mode fields
        'integration_type',
        'business_phone_number',
        'business_account_name',
        'api_status',
        'business_free_status',
        'last_sync_at',
        'sync_method',
        'api_error_message',
        'business_free_error_message',
    ];

    protected $casts = [
        'access_token' => 'encrypted',
        'webhook_secret' => 'encrypted',
        'test_result' => 'array',
        'is_enabled' => 'boolean',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
        'last_tested_at' => 'datetime',
        'last_sync_at' => 'datetime',
    ];

    protected $hidden = [
        'access_token',
        'webhook_secret',
    ];

    /**
     * Get the tenant that owns this credential
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Check if credentials are complete and valid
     */
    public function isComplete(): bool
    {
        return !empty($this->business_account_id)
            && !empty($this->phone_number_id)
            && !empty($this->access_token)
            && !empty($this->webhook_secret);
    }

    /**
     * Check if credentials are ready to use
     */
    public function isReady(): bool
    {
        return $this->is_enabled && $this->is_verified && $this->isComplete();
    }

    /**
     * Get credentials for a specific tenant
     */
    public static function forTenant(int $tenantId): ?self
    {
        return self::where('tenant_id', $tenantId)->first();
    }

    /**
     * Get enabled credentials for a specific tenant
     */
    public static function enabledForTenant(int $tenantId): ?self
    {
        return self::where('tenant_id', $tenantId)
            ->where('is_enabled', true)
            ->where('is_verified', true)
            ->first();
    }

    /**
     * Check if using Business Free mode
     */
    public function isBusinessFreeMode(): bool
    {
        return $this->integration_type === 'business_free';
    }

    /**
     * Check if using API mode
     */
    public function isApiMode(): bool
    {
        return $this->integration_type === 'api';
    }

    /**
     * Get the active integration mode
     */
    public function getActiveMode(): string
    {
        return $this->integration_type ?? 'api';
    }

    /**
     * Check if can send automated messages
     */
    public function canSendAutomated(): bool
    {
        if ($this->isApiMode()) {
            return $this->api_status === 'active' && $this->is_enabled && $this->is_verified;
        }
        return false; // Business Free mode doesn't support automation
    }

    /**
     * Check if Business Free mode is properly configured
     */
    public function isBusinessFreeConfigured(): bool
    {
        return !empty($this->business_phone_number) && !empty($this->business_account_name);
    }

    /**
     * Check if API mode is properly configured
     */
    public function isApiConfigured(): bool
    {
        return $this->isComplete();
    }

    /**
     * Get WhatsApp link for Business Free mode
     */
    public function getWhatsAppLink(string $phoneNumber, string $message = ''): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phoneNumber);
        $encodedMessage = urlencode($message);
        return "https://wa.me/{$phone}?text={$encodedMessage}";
    }

    /**
     * Mark API as active
     */
    public function markApiActive(): void
    {
        $this->update([
            'api_status' => 'active',
            'api_error_message' => null,
            'last_sync_at' => now(),
        ]);
    }

    /**
     * Mark API as error
     */
    public function markApiError(string $errorMessage): void
    {
        $this->update([
            'api_status' => 'error',
            'api_error_message' => $errorMessage,
            'last_sync_at' => now(),
        ]);
    }

    /**
     * Mark Business Free as active
     */
    public function markBusinessFreeActive(): void
    {
        $this->update([
            'business_free_status' => 'active',
            'business_free_error_message' => null,
            'last_sync_at' => now(),
        ]);
    }

    /**
     * Mark Business Free as error
     */
    public function markBusinessFreeError(string $errorMessage): void
    {
        $this->update([
            'business_free_status' => 'error',
            'business_free_error_message' => $errorMessage,
            'last_sync_at' => now(),
        ]);
    }
}
