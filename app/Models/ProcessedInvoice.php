<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProcessedInvoice extends Model
{
    protected $table = 'processed_invoices';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'ai_document_id',
        'supplier_id',
        'invoice_number',
        'invoice_date',
        'total_amount',
        'currency',
        'supplier_name',
        'supplier_contact',
        'status', // 'pending_review', 'approved', 'rejected'
        'workflow_stage', // 'uploaded', 'approved_for_processing', 'processing', 'processed', 'approved_for_inventory', 'completed'
        'reviewed_by',
        'reviewed_at',
        'approved_for_processing_by',
        'approved_for_processing_at',
        'approved_for_inventory_by',
        'approved_for_inventory_at',
        'processing_started_at',
        'processing_completed_at',
        'inventory_uploaded_at',
        'items_added_to_inventory',
        'notes',
        'excel_file_path',
        'pdf_file_path',
        'pdf_file_name',
        'pdf_file_size',
        'warehouse_id',
        'transfer_approved_by',
        'transfer_approved_at',
        'transfer_status',
        'extraction_status',
        'extraction_error',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'total_amount' => 'decimal:2',
        'reviewed_at' => 'datetime',
        'approved_for_processing_at' => 'datetime',
        'approved_for_inventory_at' => 'datetime',
        'processing_started_at' => 'datetime',
        'processing_completed_at' => 'datetime',
        'inventory_uploaded_at' => 'datetime',
        'transfer_approved_at' => 'datetime',
        'pdf_file_size' => 'integer',
    ];

    /**
     * Get the tenant this invoice belongs to
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the branch this invoice belongs to
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the AI document this invoice was processed from
     */
    public function aiDocument(): BelongsTo
    {
        return $this->belongsTo(AIDocument::class);
    }

    /**
     * Get the warehouse this invoice will be transferred to
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the user who approved the warehouse transfer
     */
    public function transferApprovedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'transfer_approved_by');
    }

    /**
     * Get the supplier for this invoice
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the user who reviewed this invoice
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Get the user who approved for processing
     */
    public function approvedForProcessingBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_for_processing_by');
    }

    /**
     * Get the user who approved for inventory
     */
    public function approvedForInventoryBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_for_inventory_by');
    }

    /**
     * Get all items in this invoice
     */
    public function items(): HasMany
    {
        return $this->hasMany(ProcessedInvoiceItem::class);
    }

    /**
     * Scope for pending review
     */
    public function scopePendingReview($query)
    {
        return $query->where('status', 'pending_review');
    }

    /**
     * Scope for uploaded stage (waiting for first approval)
     */
    public function scopeUploaded($query)
    {
        return $query->where('workflow_stage', 'uploaded');
    }

    /**
     * Scope for approved for processing (waiting to be processed)
     */
    public function scopeApprovedForProcessing($query)
    {
        return $query->where('workflow_stage', 'approved_for_processing');
    }

    /**
     * Scope for processing stage
     */
    public function scopeProcessing($query)
    {
        return $query->where('workflow_stage', 'processing');
    }

    /**
     * Scope for processed (waiting for second approval)
     */
    public function scopeProcessed($query)
    {
        return $query->where('workflow_stage', 'processed');
    }

    /**
     * Scope for approved for inventory (ready to add to inventory)
     */
    public function scopeApprovedForInventory($query)
    {
        return $query->where('workflow_stage', 'approved_for_inventory');
    }

    /**
     * Scope for completed
     */
    public function scopeCompleted($query)
    {
        return $query->where('workflow_stage', 'completed');
    }

    /**
     * Scope for approved invoices
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
}

