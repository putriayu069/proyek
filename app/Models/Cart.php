<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    // ✅ Ganti dari 'keranjang' ke 'carts'
    protected $table = 'carts';

    protected $fillable = [
        'user_id',
        'barang_id',
        'quantity',
    ];

    /**
     * 🔹 Relasi ke tabel users
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 🔹 Relasi ke tabel barangs
     */
    public function barang()
    {
        return $this->belongsTo(Barang::class, 'barang_id');
    }

    /**
     * 🔹 Accessor untuk subtotal (opsional)
     */
    public function getSubtotalAttribute()
    {
        if ($this->barang) {
            return $this->barang->harga * $this->quantity;
        }
        return 0;
    }
}
