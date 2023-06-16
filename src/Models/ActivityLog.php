<?php

namespace Mhassan654\Uraefrisapi\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $table = 'activity_logs';

    protected $fillable = [
        'api_endpoint',
        'ip_address',
        'request_time',
        'api_client',
    ];

    protected $casts = [
        'request_time' => 'datetime',
    ];
}
