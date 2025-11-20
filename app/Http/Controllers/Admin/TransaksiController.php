<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class TransaksiController extends Controller
{
    public function index()
{
    // ambil data transaksi (pakai paginate agar tabel tetap jalan)
    $transaksi = \App\Models\Transaksi::with(['user','barang'])->latest()->paginate(10);

    // total semua pemasukan (semua baris, bukan hanya halaman ini)
    $totalSemuaPembayaran = \App\Models\Transaksi::sum('total_harga');

    return view('admin.transaksi.index', compact('transaksi', 'totalSemuaPembayaran'));
}

    public function destroy($id)
    {
        $transaksi = Transaksi::findOrFail($id);
        $transaksi->delete();

        return redirect()->route('admin.transaksi.index')->with('success', 'Transaksi berhasil dihapus.');
    }
  public function download()
{
    $transaksi = Transaksi::all();
   $totalSemuaPembayaran = $transaksi->sum('total_harga');
 // pastikan ini dikirim

    $pdf = Pdf::loadView('admin.transaksi.pdf', [
        'transaksi' => $transaksi,
        'totalSemuaPembayaran' => $totalSemuaPembayaran
    ]);

    return $pdf->download('data-transaksi.pdf');
}

}