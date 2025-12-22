<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WhatsAppTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'created_by',
        'name',
        'display_name',
        'description',
        'category',
        'language',
        'header_text',
        'body_text',
        'footer_text',
        'buttons',
        'parameters',
        'whatsapp_template_id',
        'status',
        'rejection_reason',
        'usage_count',
        'last_used_at',
        'is_active',
        'is_default',
        'metadata',
    ];

    protected $casts = [
        'buttons' => 'array',
        'parameters' => 'array',
        'metadata' => 'array',
        'last_used_at' => 'datetime',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    // Relationships
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(WhatsAppMessage::class, 'template_id');
    }

    // Scopes
    public function scopeForTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeDefaults($query)
    {
        return $query->where('is_default', true);
    }

    // Methods
    public function incrementUsage()
    {
        $this->increment('usage_count');
        $this->update(['last_used_at' => now()]);
    }

    public function getParameterPlaceholders()
    {
        $placeholders = [];
        if ($this->parameters) {
            foreach ($this->parameters as $param) {
                $placeholders[] = '{{' . $param . '}}';
            }
        }
        return $placeholders;
    }

    public function replaceParameters($content, $parameters = [])
    {
        $replacedContent = $content;
        foreach ($parameters as $key => $value) {
            $replacedContent = str_replace('{{' . $key . '}}', $value, $replacedContent);
        }
        return $replacedContent;
    }

    // Status constants
    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    // Category constants
    const CATEGORY_MARKETING = 'MARKETING';
    const CATEGORY_UTILITY = 'UTILITY';
    const CATEGORY_AUTHENTICATION = 'AUTHENTICATION';
}
