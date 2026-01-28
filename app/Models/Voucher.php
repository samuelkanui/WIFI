<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    protected $fillable = [
        'code',
        'tariff_id',
        'payment_id',
        'expires_at',
        'used',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used' => 'boolean',
    ];

    public function tariff()
    {
        return $this->belongsTo(Tariff::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}
