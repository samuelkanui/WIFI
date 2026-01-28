<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'amount',
        'phone',
        'gateway',
        'transaction_id',
        'mpesa_receipt',
        'status',
        'tariff_id',
    ];

    public function tariff()
    {
        return $this->belongsTo(Tariff::class);
    }

    public function voucher()
    {
        return $this->hasOne(Voucher::class);
    }
}
