<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PrescriptionCheck extends Model
{
    protected $table = 'prescription_checks';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'ai_document_id',
        'user_id',
        'patient_name',
        'prescription_date',
        'status', // 'pending', 'completed'
        'checked_at',
    ];

    protected $casts = [
        'prescription_date' => 'date',
        'checked_at' => 'datetime',
    ];

    /**
     * Get the tenant this check belongs to
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the branch this check belongs to
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the AI document this check was based on
     */
    public function aiDocument(): BelongsTo
    {
        return $this->belongsTo(AIDocument::class);
    }

    /**
     * Get the user who performed this check
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all medications checked in this prescription
     */
    public function medications(): HasMany
    {
        return $this->hasMany(PrescriptionMedication::class);
    }
}

