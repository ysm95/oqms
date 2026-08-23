<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QmsPermit extends Model
{
    protected $guarded = [];

    protected $casts = [
        'planned_start_at' => 'datetime',
        'planned_end_at' => 'datetime',
        'issued_at' => 'datetime',
        'closed_at' => 'datetime',
        'isolation_required' => 'boolean',
        'gas_test_required' => 'boolean',
        'fire_watch_required' => 'boolean',
        'standby_required' => 'boolean',
        'loto_points' => 'array',
        'hazards' => 'array',
        'controls' => 'array',
        'required_ppe' => 'array',
        'required_training' => 'array',
        'linked_documents' => 'array',
        'approval_history' => 'array',
        'field_checks' => 'array',
    ];

    public function activities(): HasMany
    {
        return $this->hasMany(QmsPermitActivity::class)->latest();
    }

    public function canTransition(string $action): bool
    {
        return array_key_exists($action, self::allowedTransitionsFor($this->status));
    }

    public static function allowedTransitionsFor(string $status): array
    {
        return match ($status) {
            'Draft' => ['submit' => 'Pending Review', 'cancel' => 'Cancelled'],
            'Pending Review' => ['approve' => 'Approved', 'cancel' => 'Cancelled'],
            'Approved' => ['issue' => 'Active', 'cancel' => 'Cancelled'],
            'Active' => ['suspend' => 'Suspended', 'extend' => 'Active', 'close' => 'Closed'],
            'Suspended' => ['issue' => 'Active', 'extend' => 'Active', 'close' => 'Closed', 'cancel' => 'Cancelled'],
            default => [],
        };
    }
}
