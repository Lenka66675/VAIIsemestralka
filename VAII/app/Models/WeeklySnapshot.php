<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeeklySnapshot extends Model
{

    protected $table = 'backlog_snapshots';

    protected $fillable = [
        'snapshot_date',
        'backlog',
        'backlog_in_days',
        'avg_processing_days',
        'on_time_percentage',
    ];

    protected $casts = [
        'snapshot_date' => 'date',
        'backlog' => 'integer',
        'backlog_in_days' => 'integer',
        'avg_processing_days' => 'float',
        'on_time_percentage' => 'float',
    ];

    public $timestamps = false;
}
