<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tariff extends Model
{
    protected $fillable = [
        'name',
        'price',
        'duration_minutes',
        'data_limit_bytes',
        'download_speed_kbps',
        'upload_speed_kbps',
    ];

    public function vouchers()
    {
        return $this->hasMany(Voucher::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
