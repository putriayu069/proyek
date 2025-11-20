<?php

namespace App\Http\Controllers\Api;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Barang;

class ProductController extends Controller
{
    public function index()
{
    $barang = Barang::with(['vouchers' => function($q) {
        $q->where('aktif', 1)
          ->whereDate('tanggal_mulai', '<=', now())
          ->whereDate('tanggal_berakhir', '>=', now());
    }])->get()->map(function($b) {
        $voucher = $b->vouchers->first();
        $discount = $voucher?->diskon ?? 0;

        $priceAfterDiscount = $discount ? $b->harga - ($b->harga * $discount / 100) : $b->harga;

        return [
            'id' => $b->id,
            'nama' => $b->nama,
            'harga' => $b->harga,
            'harga_diskon' => $priceAfterDiscount, // harga setelah diskon
            'gambar' => $b->gambar,
            'kategori' => $b->kategori,
            'discount' => $discount,
        ];
    });

    return response()->json($barang);
}

public function show($id)
{
    $b = Barang::with(['vouchers' => function($q) {
        $q->where('aktif', 1)
          ->whereDate('tanggal_mulai', '<=', now())
          ->whereDate('tanggal_berakhir', '>=', now());
    }])->find($id);

    if (!$b) return response()->json(['message' => 'Produk tidak ditemukan'], 404);

    $voucher = $b->vouchers->first();
    $discount = $voucher?->diskon ?? 0;
    $priceAfterDiscount = $discount ? $b->harga - ($b->harga * $discount / 100) : $b->harga;

   return response()->json([
    'id' => $b->id,
    'nama' => $b->nama,
    'deskripsi' => $b->deskripsi,   // ✅ tambahkan
    'stok' => $b->stok,             // ✅ tambahkan
    'harga' => $b->harga,
    'harga_diskon' => $priceAfterDiscount,
    'gambar' => $b->gambar,
    'kategori' => $b->kategori,
    'discount' => $discount,
]);
}
}
