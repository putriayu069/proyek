<?php

namespace App\Http\Controllers\Admin;
use Illuminate\Http\Request;
use App\Models\Barang;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Controller;

class LaporanController extends Controller
{
    public function laporanBarang()
    {
        $barangs = Barang::all();
        return view('admin.laporan.barang', compact('barangs'));
    }


    public function laporanBarangPDF()
    {
        $barangs = Barang::all();
        $pdf = Pdf::loadView('admin.laporan.barang', compact('barangs'));
        return $pdf->download('laporan.barang_pdf');
    }
}
