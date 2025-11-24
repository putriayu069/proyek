<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property \App\Models\User $user
 * @property \App\Models\Barang $barang
 */
class Transaksi extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 
        'nama_user',
        'barang_id',
        'total_harga',
        'status_pembayaran',
        'nama_barang',
        'tanggal_transaksi',
        'alamat_pengiriman',
        'ongkir',
        'kurir',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'barang_id');
    }
}
