<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Midtrans\Snap;
use Midtrans\Config;
use App\Models\User;
use App\Models\Transaksi;
use App\Models\Barang;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PembayaranController extends Controller
{
    public function index(Request $request)
    {
        // Tampilkan halaman pembayaran seperti sebelumnya,
        // TAPI jangan buat Snap token di sini — token dibuat setelah user pilih ongkir
        $selectedItems = $request->input('produk', []);
        $cart = session()->get('cart', []);

        $selectedItems = array_map('strval', $selectedItems);
        $selectedCartItems = array_intersect_key($cart, array_flip($selectedItems));
        if (empty($selectedCartItems)) {
            return redirect()->back()->with('error', 'Tidak ada item yang dipilih untuk checkout!');
        }

        $total = 0;
        foreach ($selectedCartItems as $id => $item) {
            $jumlah = $item['jumlah'] ?? 1;
            $total += ($item['harga'] ?? 0) * $jumlah;
        }

        session(['selected_items' => $selectedItems]);

        return view('pembayaran', [
            'selectedCartItems' => $selectedCartItems,
            'total' => $total,
        ]);
    }

    // New: create snap token dynamically (AJAX)
    public function createSnapToken(Request $request)
    {
        $request->validate([
            'ongkir' => 'required|integer|min:0',
            'kurir' => 'required|string',
            'service' => 'nullable|string',
            'alamat_pengiriman' => 'nullable|string',
            'total_barang' => 'required|numeric|min:0',
        ]);

        /** @var \App\Models\User|null $user */
        $user = $request->user(); 


        // Siapkan konfigurasi Midtrans
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$clientKey = config('services.midtrans.client_key');
        Config::$isProduction = config('services.midtrans.is_production', false);
        Config::$isSanitized = true;
        Config::$is3ds = true;

        $totalBarang = (int) $request->total_barang;
        $ongkir = (int) $request->ongkir;
        $grandTotal = $totalBarang + $ongkir;

        // Ambil item details dari session (kamu bisa kirim juga jika mau)
        $cart = session()->get('cart', []);
        $selectedItems = session()->get('selected_items', []);
        $selectedItems = array_map('strval', $selectedItems);
        $selectedCartItems = array_intersect_key($cart, array_flip($selectedItems));

        $item_details = [];
        foreach ($selectedCartItems as $id => $item) {
            $item_details[] = [
                'id' => $item['barang_id'] ?? $id,
                'price' => (int) ($item['harga'] ?? 0),
                'quantity' => (int) ($item['jumlah'] ?? 1),
                'name' => $item['nama'] ?? 'Produk',
            ];
        }

        // Tambahkan satu item khusus "ONGKIR" agar muncul di Snap summary (opsional)
        if ($ongkir > 0) {
            $item_details[] = [
                'id' => 'ONGKIR',
                'price' => $ongkir,
                'quantity' => 1,
                'name' => 'Ongkos Kirim (' . strtoupper($request->kurir) . ')',
            ];
        }

        $params = [
            'transaction_details' => [
                'order_id' => (string) Str::uuid(),
                'gross_amount' => $grandTotal,
            ],
            'item_details' => $item_details,
            'customer_details' => [
                'first_name' => $user?->name ?? 'Pengguna', // ✅ gunakan ?-> untuk aman jika null
                'email' => $user?->email ?? 'user@example.com',
            ],
        ];

        try {
            $snapToken = Snap::getSnapToken($params);
        } catch (\Exception $e) {
            Log::error('Error creating Snap token: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal membuat snap token'], 500);
        }

        // Kembalikan token + order_id supaya frontend bisa menyimpan order_id (jika perlu)
        return response()->json([
            'snap_token' => $snapToken,
            'order_id' => $params['transaction_details']['order_id'],
            'grand_total' => $grandTotal,
        ]);
    }

    public function proses(Request $request)
    {
        Log::info('proses method dipanggil');

        $cart = session()->get('cart', []);
        $selectedItems = session()->get('selected_items', []);
        $selectedItems = array_map('strval', $selectedItems);
        $user_id = Auth::id();

        $selectedCartItems = array_intersect_key($cart, array_flip($selectedItems));
        Log::info('Selected Cart Items at proses:', ['items' => $selectedCartItems]);

        $paymentResult = json_decode($request->payment_result, true);
        Log::info('Hasil pembayaran Midtrans:', $paymentResult);

        $alamat_pengiriman = $request->input('alamat_pengiriman', null);
        $ongkir = (int) $request->input('ongkir', 0);
        $kurir = $request->input('kurir', null);
        $service = $request->input('service', null);

        // HANDLE BELI SEKARANG
        if (session()->has('beli_sekarang_item')) {
            $item = session('beli_sekarang_item');
            $barang = Barang::with('vouchers')->find($item['barang_id']);

            if (!$barang || $barang->stok < 1) {
                return redirect()->back()->with('error', 'Stok untuk ' . $item['nama_barang'] . ' tidak mencukupi!');
            }

            // Cek voucher aktif (sama seperti sebelumnya)
            $voucherAktif = $barang->vouchers
                ->filter(fn($v) =>
                    $v->aktif &&
                    (!$v->masa_berlaku || now()->lte(Carbon::parse($v->masa_berlaku))) &&
                    (!$v->batas_penggunaan || $v->jumlah_digunakan < $v->batas_penggunaan)
                )
                ->first();

            $hargaAkhir = $voucherAktif ? $barang->harga * (1 - $voucherAktif->diskon / 100) : $barang->harga;

            Transaksi::create([
                'user_id' => $item['user_id'],
                'nama_user' => $item['nama_user'],
                'nama_barang' => $item['nama_barang'],
                'barang_id' => $item['barang_id'],
                'total_harga' => $hargaAkhir + $ongkir, // tambahkan ongkir
                'ongkir' => $ongkir,
                'kurir' => $kurir,
                'service' => $service,
                'alamat_pengiriman' => $alamat_pengiriman,
                'status_pembayaran' => 'Lunas',
                'kode_transaksi' => $paymentResult['transaction_id'] ?? 'manual',
            ]);

            $jumlahBeli = $item['jumlah'] ?? 1;
            $barang->decrement('stok', $jumlahBeli);

            if ($voucherAktif) {
                $voucherAktif->increment('jumlah_digunakan', $jumlahBeli);
            }

            session()->forget('beli_sekarang_item');
        }

        // HANDLE DARI CART
        if (empty($selectedCartItems)) {
            Log::warning('Tidak ada item yang dipilih saat proses pembayaran');
            return redirect()->route('dashboard')->with('error', 'Tidak ada item yang dipilih.');
        }

        foreach ($selectedCartItems as $item) {
            $barang = Barang::with('vouchers')->find($item['barang_id']);
            $jumlahBeli = $item['jumlah'] ?? 1;

            if (!$barang || $barang->stok < $jumlahBeli) {
                return redirect()->back()->with('error', 'Stok untuk ' . $item['nama'] . ' tidak mencukupi!');
            }

            // Cek voucher aktif
            $voucherAktif = $barang->vouchers
                ->filter(fn($v) =>
                    $v->aktif &&
                    (!$v->masa_berlaku || now()->lte(Carbon::parse($v->masa_berlaku))) &&
                    (!$v->batas_penggunaan || $v->jumlah_digunakan < $v->batas_penggunaan)
                )
                ->first();

            $hargaAkhir = $voucherAktif ? $barang->harga * (1 - $voucherAktif->diskon / 100) : $barang->harga;

            // Simpan transaksi: untuk kemudahan aku menyimpan 1 baris per produk,
            // tapi kita tambahkan ongkir hanya pada baris pertama agar tidak double count.
            Transaksi::create([
                'user_id' => $user_id,
                'nama_user' => Auth::user()->name,
                'nama_barang' => $item['nama'],
                'barang_id' => $item['barang_id'],
                'total_harga' => ($hargaAkhir * $jumlahBeli) + 0, // ongkir ditambahkan setelah loop
                'ongkir' => 0,
                'kurir' => null,
                'service' => null,
                'alamat_pengiriman' => null,
                'status_pembayaran' => 'Lunas',
                'kode_transaksi' => $paymentResult['transaction_id'] ?? 'manual',
            ]);

            $barang->decrement('stok', $jumlahBeli);

            if ($voucherAktif) {
                $voucherAktif->increment('jumlah_digunakan', $jumlahBeli);
            }
        }

        // Jika ada ongkir, tambahkan satu row ringkasan / atau update salah satu transaksi:
        if ($ongkir > 0) {
            // Pilihan: simpan sebagai record tambahan berisi ongkir (atau update record pertama)
            Transaksi::create([
                'user_id' => $user_id,
                'nama_user' => Auth::user()->name,
                'nama_barang' => 'Biaya Pengiriman',
                'barang_id' => null,
                'total_harga' => $ongkir,
                'ongkir' => $ongkir,
                'kurir' => $kurir,
                'service' => $service,
                'alamat_pengiriman' => $alamat_pengiriman,
                'status_pembayaran' => 'Lunas',
                'kode_transaksi' => $paymentResult['transaction_id'] ?? 'manual',
            ]);
        } else {
            // Jika ingin menyimpan alamat meskipun ongkir = 0, bisa update salah satu Transaksi
            // (di-skip untuk singkat)
        }

        // Hapus item dari cart
        foreach ($selectedItems as $itemId) {
            unset($cart[$itemId]);
        }
        session(['cart' => $cart]);
        session()->forget('selected_items');

        return redirect()->route('dashboard')->with('success', 'Pembayaran berhasil!');
    }
   
    public function beliSekarang(Request $request)
    {
        // --- Langkah 1: Pengecekan ID Produk ---
        $barang_id = $request->input('product_id');

        if (!$barang_id) {
            // Redirect jika 'product_id' tidak terkirim dari form
            Log::error('DEBUG ERROR: beliSekarang gagal - product_id kosong.');
            return redirect()->back()->with('error', 'DEBUG-1: Gagal memproses. ID Produk **(product_id)** tidak ditemukan di form.');
        }

        // --- Langkah 2: Pengecekan Barang di Database ---
        try {
            $barang = Barang::findOrFail($barang_id);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Redirect jika Barang tidak ada di database
            Log::error('DEBUG ERROR: beliSekarang gagal - Barang ID:' . $barang_id . ' tidak ditemukan.');
            return redirect()->back()->with('error', 'DEBUG-2: Gagal memproses. Barang dengan ID **' . $barang_id . '** tidak ditemukan di database.');
        }

        /** @var \App\Models\User $user */
        $user = $request->user();

        // --- Langkah 3: Pengecekan Status Login ---
        if (!$user) {
            // Redirect jika user belum login (Middleware Auth mungkin sudah menanganinya, tapi ini adalah safety check)
            Log::warning('DEBUG WARNING: beliSekarang gagal - User belum login.');
            return redirect()->route('login')->with('error', 'DEBUG-3: Anda harus login untuk melanjutkan pembelian.');
        }

        // --- Langkah 4: Hitung Total Harga (Opsional: Logging Harga) ---
        $total_harga = $request->input('harga') ?? $barang->harga;
        if ($total_harga <= 0) {
            Log::error('DEBUG ERROR: beliSekarang gagal - Total harga 0 atau kurang.');
            return redirect()->back()->with('error', 'DEBUG-4: Gagal memproses. Harga barang tidak valid atau nol: ' . $total_harga);
        }

        // --- Langkah 5: Pembuatan Transaksi (Jika gagal, mungkin karena validasi/relasi) ---
        try {
            $transaksi = Transaksi::create([
                'user_id' => $user->id,
                'nama_user' => $user->name,
                'barang_id' => $barang->id,
                'nama_barang' => $barang->nama_barang ?? $barang->nama ?? 'Barang',
                'total_harga' => $total_harga,
                'status_pembayaran' => 'pending',
                'tanggal_transaksi' => now(),
                'alamat_pengiriman' => $request->alamat_pengiriman ?? null,
                'ongkir' => $request->ongkir ?? 0,
                'kurir' => $request->kurir ?? null,
            ]);
            // Log keberhasilan pembuatan transaksi
            Log::info('DEBUG INFO: Transaksi ID ' . $transaksi->id . ' berhasil dibuat.');

        } catch (\Exception $e) {
            Log::error('DEBUG ERROR: beliSekarang gagal pada pembuatan Transaksi: ' . $e->getMessage());
            return redirect()->back()->with('error', 'DEBUG-5: Gagal membuat entri Transaksi di database. Pesan: ' . $e->getMessage());
        }

        // --- Langkah 6: Konfigurasi dan Inisialisasi Midtrans ---
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = false;
        Config::$isSanitized = true;
        Config::$is3ds = true;

        $midtransParams = [
            'transaction_details' => [
                'order_id' => $transaksi->id,
                'gross_amount' => (int) $total_harga,
            ],
            'customer_details' => [
                'first_name' => $user->name,
                'email' => $user->email,
            ],
            'item_details' => [
                [
                    'id' => $barang->id,
                    'price' => (int) $total_harga, // Gunakan harga akhir yang sudah dihitung
                    'quantity' => 1,
                    'name' => $barang->nama,
                ]
            ]
        ];

        try {
            $snapToken = Snap::getSnapToken($midtransParams);
            // Log keberhasilan mendapatkan token
            Log::info('DEBUG INFO: Snap Token berhasil didapatkan untuk Transaksi ID ' . $transaksi->id);

        } catch (\Exception $e) {
            // Redirect jika Midtrans gagal (misalnya Server Key salah, koneksi bermasalah)
            Log::error('DEBUG ERROR: beliSekarang gagal mendapatkan Snap Token: ' . $e->getMessage());
            return redirect()->back()->with('error', 'DEBUG-6: Gagal mendapatkan Snap Token Midtrans. Pesan: ' . $e->getMessage());
        }

        // --- Langkah 7: Berhasil, Lanjutkan ke View Pembayaran ---
        Log::info('DEBUG INFO: Redirecting to pembayaran view. Transaksi ID ' . $transaksi->id);
            return view('pembayaran', [
            'transaksi' => $transaksi,
            'barang' => $barang,
            'snapToken' => $snapToken
        ]);
    }


    // ... method lain seperti beliSekarang, bayar, riwayat, adminIndex, store tetap dipertahankan (bila perlu sesuaikan serupa)
}
