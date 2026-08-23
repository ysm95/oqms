<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QmsOccurrence extends Model
{
    protected $guarded = [];

    protected $casts = [
        'confidential' => 'boolean',
        'mor' => 'boolean',
        'flight_cancelled' => 'boolean',
        'event_categories' => 'array',
        'personnel_involved' => 'array',
        'action_taken' => 'array',
        'event_date' => 'date',
        'observed_on' => 'date',
        'reported_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'action_required' => 'boolean',
    ];
}
