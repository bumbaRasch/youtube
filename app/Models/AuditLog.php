<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\AuditLog
 *
 * Minimal Eloquent model for storing extraction audit events.
 */
class AuditLog extends Model
{
    use HasFactory;

    protected $table = 'audit_logs';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'request_id',
        'outcome',
        'duration_ms',
        'client_ip',
        'input_url',
    ];
}
