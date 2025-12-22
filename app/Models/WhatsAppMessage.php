<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WhatsAppMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'customer_id',
        'template_id',
        'recipient_phone',
        'message_type',
        'message_content',
        'template_parameters',
        'media_url',
        'media_type',
        'whatsapp_message_id',
        'status',
        'error_message',
        'sent_at',
        'delivered_at',
        'read_at',
        'bulk_message_id',
        'is_bulk_message',
        'bulk_filters',
        'metadata',
        'cost',
    ];

    protected $casts = [
        'template_parameters' => 'array',
        'bulk_filters' => 'array',
        'metadata' => 'array',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
        'is_bulk_message' => 'boolean',
        'cost' => 'decimal:4',
    ];

    // Relationships
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(WhatsAppTemplate::class, 'template_id');
    }

    // Scopes
    public function scopeForTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeBulkMessages($query)
    {
        return $query->where('is_bulk_message', true);
    }

    public function scopeIndividualMessages($query)
    {
        return $query->where('is_bulk_message', false);
    }

    // Accessors & Mutators
    public function getFormattedCostAttribute()
    {
        return $this->cost ? '$' . number_format($this->cost, 4) : null;
    }

    public function getIsDeliveredAttribute()
    {
        return in_array($this->status, ['delivered', 'read']);
    }

    public function getIsReadAttribute()
    {
        return $this->status === 'read';
    }

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_SENT = 'sent';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_READ = 'read';
    const STATUS_FAILED = 'failed';

    // Message type constants
    const TYPE_TEXT = 'text';
    const TYPE_TEMPLATE = 'template';
    const TYPE_MEDIA = 'media';
}
