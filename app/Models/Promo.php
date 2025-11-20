<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promo extends Model
{
    protected $table = 'promos';

    protected $fillable = [
        'kode',
        'type',
        'percent',
        'amount',
        'product_id',
        'expired_at',
    ];

    protected $casts = [
        'expired_at' => 'datetime',
    ];

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'product_id');
    }
}
