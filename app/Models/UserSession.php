<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSession extends Model
{
    protected $fillable = [
        'voucher_code',
        'mac_address',
        'ip_address',
        'started_at',
        'ended_at',
        'bytes_in',
        'bytes_out',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];
}
