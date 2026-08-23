<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QmsPermitActivity extends Model
{
    protected $guarded = [];

    protected $casts = [
        'payload' => 'array',
    ];

    public function permit(): BelongsTo
    {
        return $this->belongsTo(QmsPermit::class, 'qms_permit_id');
    }
}
