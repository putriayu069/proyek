<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Barang;
use App\Models\Voucher;
use App\Models\Promo;
use App\Models\Cart;

class KeranjangController extends Controller
{
    public function index()
    {
        $cart = session()->get('cart', []);
        return view('keranjang', compact('cart'));
    }

    public function tambah(Request $request)
    {
        $barang = Barang::with('vouchers')->findOrFail($request->barang_id);

        $cart = session()->get('cart', []);

        // Hitung diskon voucher per item
        $harga_asli = $barang->harga;
        $harga_diskon = $harga_asli;
        $diskon_persen = 0;
        if ($barang->vouchers->count() > 0) {
            $voucher = $barang->vouchers->first();
            $harga_diskon = $harga_asli * (1 - $voucher->diskon / 100);
            $diskon_persen = $voucher->diskon;
        }

        if (isset($cart[$barang->id])) {
            $cart[$barang->id]['jumlah'] += 1;
        } else {
            $cart[$barang->id] = [
                'barang_id' => $barang->id,
                'nama'      => $barang->nama,
                'harga'     => $harga_asli,
                'harga_diskon' => $harga_diskon,
                'diskon_persen' => $diskon_persen,
                'gambar'    => $barang->gambar,
                'jumlah'    => 1,
                'stok'      => $barang->stok,
            ];
        }

        session()->put('cart', $cart);

        return redirect()->back()->with('success', 'Barang ditambahkan ke keranjang!');
    }

    public function hapus(Request $request)
    {
        $barangId = $request->input('barang_id');
        $cart = session()->get('cart', []);

        if (isset($cart[$barangId])) {
            unset($cart[$barangId]);
            session()->put('cart', $cart);
        }

        // Hapus promo jika ada, karena item berubah
        session()->forget('promo');
        session()->forget('promo_code');

        return redirect()->back()->with('success', 'Barang berhasil dihapus dari keranjang.');
    }

    public function checkout(Request $request)
    {
        $cart = session()->get('cart', []);

        if (empty($request->produk) || empty($cart)) {
            return redirect()->back()->with('error', 'Tidak ada item terpilih untuk checkout!');
        }

        // Validasi stok untuk item terpilih
        foreach ($request->produk as $id) {
            if (!isset($cart[$id])) continue;
            $barang = Barang::find($id);
            $item = $cart[$id];
            if (!$barang || $barang->stok < $item['jumlah']) {
                return redirect()->back()->with('error', "Stok barang '{$item['nama']}' tidak mencukupi (stok: {$barang->stok}, dibutuhkan: {$item['jumlah']}).");
            }
        }

        // Hitung total hanya untuk item terpilih (dengan diskon voucher per item)
        $totalJumlah = 0;
        $totalHargaAsli = 0;
        $totalHargaDiskon = 0;
        $selectedCart = [];

        foreach ($request->produk as $id) {
            if (isset($cart[$id])) {
                $item = $cart[$id];
                $selectedCart[$id] = $item;
                $totalJumlah += $item['jumlah'];
                $totalHargaAsli += $item['harga'] * $item['jumlah'];
                $totalHargaDiskon += $item['harga_diskon'] * $item['jumlah'];
            }
        }

        // Terapkan promo jika ada
        $promoData = session('promo');
        $diskonPromo = 0;
        $totalAkhir = $totalHargaDiskon;

        if ($promoData) {
            if (isset($promoData['percent']) && $promoData['percent'] > 0) {
                $diskonPromo = $totalHargaDiskon * ($promoData['percent'] / 100);
            } elseif (isset($promoData['amount']) && $promoData['amount'] > 0) {
                $diskonPromo = min($promoData['amount'], $totalHargaDiskon);
            }
            $totalAkhir = $totalHargaDiskon - $diskonPromo;
        }

        // Hapus hanya item terpilih dari cart
        foreach ($request->produk as $id) {
            if (isset($cart[$id])) {
                unset($cart[$id]);
            }
        }
        session()->put('cart', $cart);

        return view('pembayaran', compact('selectedCart', 'totalJumlah', 'totalHargaAsli', 'totalHargaDiskon', 'diskonPromo', 'totalAkhir'));
    }

    public function beliSekarang(Request $request)
    {
        $barang = Barang::with('vouchers')->findOrFail($request->product_id);

        if ($barang->stok <= 0) {
            return redirect()->back()->with('error', 'Stok barang habis, tidak bisa dibeli.');
        }

        $harga_asli = $barang->harga;
        $harga_diskon = $harga_asli;
        $diskon_persen = 0;
        
        if ($barang->vouchers->count() > 0) {
            $voucher = $barang->vouchers->first();
            $harga_diskon = $harga_asli * (1 - $voucher->diskon / 100);
            $diskon_persen = $voucher->diskon;
        }

        $cart = [];
        $cart[$barang->id] = [
            'barang_id' => $barang->id,
            'nama'      => $barang->nama,
            'harga'     => $harga_asli,
            'harga_diskon' => $harga_diskon,
            'diskon_persen' => $diskon_persen,
            'gambar'    => $barang->gambar,
            'jumlah'    => 1,
            'stok'      => $barang->stok,
        ];

        session(['cart' => $cart]);

        return redirect()->route('pembayaran');
    }

    public function pembayaranSukses()
    {
        session()->forget('cart');
        session()->forget('promo');
        session()->forget('promo_code');
    }

    public function tambahKeranjang(Request $request, $id)
    {
        $produk = Barang::with('vouchers')->findOrFail($id);

        if ($produk->stok <= 0) {
            return redirect()->back()->with('error', 'Stok produk habis.');
        }

        $cart = session()->get('cart', []);

        $harga_asli = $produk->harga;
        $harga_diskon = $harga_asli;
        $diskon_persen = 0;
        
        if ($produk->vouchers->count() > 0) {
            $voucher = $produk->vouchers->first();
            $harga_diskon = $harga_asli * (1 - $voucher->diskon / 100);
            $diskon_persen = $voucher->diskon;
        }

        if (isset($cart[$id])) {
            if ($cart[$id]['jumlah'] + 1 > $produk->stok) {
                return redirect()->back()->with('error', 'Jumlah barang di keranjang melebihi stok tersedia.');
            }
            $cart[$id]['jumlah'] += 1;
        } else {
            $cart[$id] = [
                'barang_id' => $id,
                'nama'   => $produk->nama,
                'harga'  => $harga_asli,
                'harga_diskon' => $harga_diskon,
                'diskon_persen' => $diskon_persen,
                'gambar' => $produk->gambar,
                'jumlah' => 1,
                'stok'   => $produk->stok,
            ];
        }

        session()->put('cart', $cart);

        return redirect()->back()->with('success', 'Produk berhasil ditambahkan ke keranjang');
    }

    public function update(Request $request)
    {
        $cart = session()->get('cart', []);
        $id   = $request->barang_id;

        if (isset($cart[$id])) {
            $barang = Barang::with('vouchers')->find($id);

            if ($barang) {
                $harga_asli = $barang->harga;
                $harga_diskon = $harga_asli;
                $diskon_persen = 0;

                if ($barang->vouchers->count() > 0) {
                    $voucher = $barang->vouchers
                        ->filter(fn($v) =>
                            $v->aktif &&
                            (!$v->masa_berlaku || \Carbon\Carbon::now()->lte(\Carbon\Carbon::parse($v->masa_berlaku))) &&
                            (!$v->batas_penggunaan || $v->jumlah_digunakan < $v->batas_penggunaan)
                        )
                        ->first();

                    if ($voucher) {
                        $harga_diskon = $harga_asli * (1 - $voucher->diskon / 100);
                        $diskon_persen = $voucher->diskon;
                    }
                }

                 if ($barang->stok <= 0) {
                $cart[$id]['jumlah'] = 0;
            } else {
                if ($request->action === 'increase') {
                    $cart[$id]['jumlah'] = min($cart[$id]['jumlah'] + 1, $barang->stok);
                } elseif ($request->action === 'decrease') {
                    $cart[$id]['jumlah'] = max(1, $cart[$id]['jumlah']);
                }
            }
            $cart[$id]['harga_diskon'] = $harga_diskon;
            $cart[$id]['diskon_persen'] = $diskon_persen;
            session()->put('cart', $cart);
            }
        }

        // Validasi ulang promo jika ada
        if (session('promo')) {
            $promo = Promo::find(session('promo')['promo_id']);
            $today = now()->toDateString();
            $isValid = true;

            if (!$promo ||
                (isset($promo->status) && strtolower($promo->status) !== 'aktif') ||
                (isset($promo->tanggal_mulai) && $promo->tanggal_mulai > $today) ||
                (isset($promo->tanggal_berakhir) && $promo->tanggal_berakhir < $today) ||
                (isset($promo->batas_penggunaan) && isset($promo->jumlah_digunakan) && $promo->jumlah_digunakan >= $promo->batas_penggunaan)) {
                $isValid = false;
            }

            if ($isValid) {
                // Hitung ulang selectedTotal untuk produk di promo
                $selectedTotal = 0;
                foreach (session('promo')['produk'] as $prodId) {
                    if (isset($cart[$prodId])) {
                        $selectedTotal += $cart[$prodId]['harga_diskon'] * $cart[$prodId]['jumlah'];
                    }
                }

                // Cek minimum pembelian
                if (isset(session('promo')['min_pembelian']) && $selectedTotal < session('promo')['min_pembelian']) {
                    $isValid = false;
                }
            }

            if (!$isValid) {
                session()->forget('promo');
                session()->forget('promo_code');
            }
        }

        return back();
    }

    public function applyPromo(Request $request)
    {
        // Validasi input
        $request->validate([
            'promo_code' => 'required|string',
            'produk' => 'required|array|min:1'
        ], [
            'produk.required' => 'Pilih minimal 1 barang terlebih dahulu!',
            'produk.min' => 'Pilih minimal 1 barang terlebih dahulu!'
        ]);

        $promo = Promo::where('kode', $request->promo_code)->first();

        if (!$promo) {
            return back()->with('promo_error', 'Kode promo tidak ditemukan.');
        }

        // Validasi status promo (jika ada kolom status)
        if (isset($promo->status) && strtolower($promo->status) !== 'aktif') {
            return back()->with('promo_error', 'Promo tidak aktif.');
        }

        // Validasi tanggal (jika ada kolom tanggal)
        $today = now()->toDateString();
        if (isset($promo->tanggal_mulai) && $promo->tanggal_mulai > $today) {
            return back()->with('promo_error', 'Promo belum aktif.');
        }
        if (isset($promo->tanggal_berakhir) && $promo->tanggal_berakhir < $today) {
            return back()->with('promo_error', 'Promo sudah kadaluarsa.');
        }

        // Validasi batas penggunaan (jika ada)
        if (isset($promo->batas_penggunaan) && isset($promo->jumlah_digunakan)) {
            if ($promo->jumlah_digunakan >= $promo->batas_penggunaan) {
                return back()->with('promo_error', 'Promo sudah mencapai batas penggunaan.');
            }
        }

        // Hitung total dari item yang dipilih (setelah diskon voucher)
        $cart = session()->get('cart', []);
        $selectedTotal = 0;

        foreach ($request->produk as $id) {
            if (isset($cart[$id])) {
                $item = $cart[$id];
                $selectedTotal += $item['harga_diskon'] * $item['jumlah'];
            }
        }

        // Validasi minimum pembelian (jika ada)
        if (isset($promo->min_pembelian) && $selectedTotal < $promo->min_pembelian) {
            return back()->with('promo_error', 'Minimum pembelian untuk promo ini adalah Rp. ' . number_format($promo->min_pembelian, 0, ',', '.'));
        }

        // Hitung diskon
        $diskon = 0;
        if (isset($promo->percent) && $promo->percent > 0) {
            $diskon = $selectedTotal * ($promo->percent / 100);
        } elseif (isset($promo->amount) && $promo->amount > 0) {
            $diskon = min($promo->amount, $selectedTotal); // Diskon tidak boleh lebih dari total
        }

        // Pastikan diskon tidak melebihi total
        $diskon = min($diskon, $selectedTotal);
        $totalAkhir = $selectedTotal - $diskon;

        // Simpan data promo ke session
        session([
            'promo' => [
                'kode' => $promo->kode,
                'percent' => $promo->percent ?? 0,
                'amount' => $promo->amount ?? 0,
                'min_pembelian' => $promo->min_pembelian ?? 0,
                'diskon' => $diskon,
                'total_akhir' => $totalAkhir,
                'produk' => $request->produk,
                'promo_id' => $promo->id
            ],
            'promo_code' => $promo->kode
        ]);

        return back()->with('promo_success', 'Kode promo berhasil diterapkan! Anda hemat Rp. ' . number_format($diskon, 0, ',', '.'));
    }

    public function removePromo()
    {
        session()->forget(['promo', 'promo_code']);
        return back()->with('promo_success', 'Promo berhasil dihapus.');
    }

    public function checkVoucher(Request $request)
    {
        $request->validate([
            'kode' => 'required'
        ]);

        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return response()->json(['success' => false, 'message' => 'Keranjang kosong'], 400);
        }

        $items = [];
        foreach ($cart as $item) {
            if (!empty($item['barang_id'])) {
                $items[] = ['barang_id' => $item['barang_id']];
            }
        }

        $voucher = Voucher::with('barangs')->where('kode', $request->kode)->first();

        if (!$voucher) {
            return response()->json(['success' => false, 'message' => 'Kode voucher tidak ditemukan'], 404);
        }

        if (strtolower($voucher->status) !== 'aktif') {
            return response()->json(['success' => false, 'message' => 'Voucher tidak aktif'], 400);
        }

        $today = now()->toDateString();
        if ($voucher->tanggal_mulai && $voucher->tanggal_mulai > $today) {
            return response()->json(['success' => false, 'message' => 'Voucher belum aktif'], 400);
        }
        if ($voucher->tanggal_berakhir && $voucher->tanggal_berakhir < $today) {
            return response()->json(['success' => false, 'message' => 'Voucher sudah kadaluarsa'], 400);
        }

        if ($voucher->batas_penggunaan && $voucher->jumlah_digunakan >= $voucher->batas_penggunaan) {
            return response()->json(['success' => false, 'message' => 'Voucher sudah mencapai batas penggunaan'], 400);
        }

        $barangIds = $voucher->barangs->pluck('id')->toArray();

        $eligibleItems = collect($items)
            ->filter(fn($item) => in_array($item['barang_id'], $barangIds))
            ->values();

        if ($eligibleItems->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Voucher tidak berlaku untuk barang dalam keranjang'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'diskon' => $voucher->diskon,
            'berlaku_pada' => $eligibleItems,
        ]);
    }
}
