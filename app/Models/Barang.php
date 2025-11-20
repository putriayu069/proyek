<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    protected $table = 'barangs';

    protected $fillable = [
        'order_id',
        'nama',
        'deskripsi',
        'stok',
        'harga',
        'gambar',
        'kategori',
    ];

    // Semua voucher yang pernah terkait
    public function vouchers() {
        return $this->belongsToMany(Voucher::class, 'voucher_barang');
    }

    // Voucher aktif saat ini
    public function voucherAktif() {
        $today = now()->toDateString();
        return $this->belongsToMany(Voucher::class, 'voucher_barang')
                    ->where('aktif', 1)
                    ->where(function($query) use ($today) {
                        $query->whereNull('tanggal_mulai')->orWhere('tanggal_mulai', '<=', $today);
                    })
                    ->where(function($query) use ($today) {
                        $query->whereNull('tanggal_berakhir')->orWhere('tanggal_berakhir', '>=', $today);
                    })
                    ->orderByDesc('diskon') // ambil diskon terbesar
                    ->limit(1);
    }
}
