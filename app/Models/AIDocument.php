<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AIDocument extends Model
{
    protected $table = 'ai_documents';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'user_id',
        'document_type', // 'invoice', 'prescription'
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
        'status', // 'pending', 'processing', 'completed', 'failed'
        'processing_error',
        'raw_text',
        'extracted_data',
        'processed_at',
    ];

    protected $casts = [
        'extracted_data' => 'array',
        'processed_at' => 'datetime',
    ];

    /**
     * Get the tenant this document belongs to
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the branch this document belongs to
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the user who uploaded this document
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all processed invoices from this document
     */
    public function processedInvoices(): HasMany
    {
        return $this->hasMany(ProcessedInvoice::class);
    }

    /**
     * Get all prescription checks from this document
     */
    public function prescriptionChecks(): HasMany
    {
        return $this->hasMany(PrescriptionCheck::class);
    }

    /**
     * Scope for pending documents
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for completed documents
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}

