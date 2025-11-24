<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Halaman Pembayaran</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" />

    {{-- PASTIKAN config('midtrans.client_key') MENGAMBIL DARI config/midtrans.php --}}
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" 
        data-client-key="{{ config('midtrans.client_key') }}"></script>

    <style>
        body { background-color: #f1f3f5; }
        .card-custom { background: #fff; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); padding: 30px; margin-top: 40px; margin-bottom: 40px; }
        .btn-primary { background-color: #0bc455; border: none; font-weight: 600; padding: 12px 24px; border-radius: 10px; transition: background-color 0.3s ease; }
        .btn-primary:hover { background-color: #0aa64b; }
        .text-muted.strikethrough { text-decoration: line-through; }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            <div class="card card-custom">
                <h2 class="mb-4 text-center">Pembayaran</h2>

                <form id="payment-form" method="POST" action="{{ route('pembayaran.proses') }}">
                    @csrf
                    <input type="hidden" name="payment_result" id="payment-result">
                    <input type="hidden" name="ongkir" id="ongkir" value="0">
                    <input type="hidden" name="kurir" id="kurir_input">
                    <input type="hidden" name="service" id="service_input">
                    <input type="hidden" name="alamat_pengiriman" id="alamat_pengiriman_input">
                    
                    {{-- ID Transaksi di-embed agar bisa di-proses di proses pembayaran --}}
                    <input type="hidden" name="transaksi_id" value="{{ $transaksi->id }}"> 

                    <div class="mb-3">
                        <input type="text" class="form-control mb-2" value="{{ auth()->user()->name }}" readonly />
                        <input type="email" class="form-control mb-2" value="{{ auth()->user()->email }}" readonly />
                        <input type="text" id="user-phone" class="form-control mb-2" value="{{ auth()->user()->phone ?? 'Belum ada No. HP' }}" readonly />
                        <textarea id="user-address" class="form-control mb-2" placeholder="Masukkan alamat lengkap pengiriman (contoh: Jalan ...)" >{{ auth()->user()->address ?? '' }}</textarea>
                    </div>

                    {{-- Produk / Ringkasan --}}
                    @php
                        // Total Barang diambil dari transaksi yang sudah dibuat (sudah termasuk diskon)
                        $total = (int) $transaksi->total_harga; 
                    @endphp

                    <div class="table-responsive mb-4">
                        <table class="table table-bordered align-middle">
                            <thead class="table-light">
                                <tr><th>Produk</th><th>Harga Satuan</th><th>Jumlah</th><th>Subtotal</th></tr>
                            </thead>
                            <tbody>
                                {{-- Menampilkan detail 1 item yang dibeli (Beli Sekarang) --}}
                                <tr>
                                    <td>{{ $barang->nama }}</td>
                                    {{-- Tampilkan harga awal untuk referensi --}}
                                    <td>
                                        @if ($barang->harga != $transaksi->total_harga)
                                            <span class="text-muted strikethrough me-2">Rp {{ number_format($barang->harga, 0, ',', '.') }}</span>
                                        @endif
                                        <span>Rp {{ number_format($transaksi->total_harga, 0, ',', '.') }}</span>
                                    </td>
                                    <td>1</td> {{-- Jumlah: 1 unit --}}
                                    <td>Rp {{ number_format($transaksi->total_harga, 0, ',', '.') }}</td>
                                </tr>
                                <tr class="table-secondary fw-bold">
                                    <td colspan="3" class="text-end">Total Barang</td>
                                    <td id="total-barang">Rp {{ number_format($total, 0, ',', '.') }}</td>
                                </tr>
                                <tr class="table-secondary fw-bold">
                                    <td colspan="3" class="text-end">Ongkir</td>
                                    <td id="total-ongkir">Rp 0</td>
                                </tr>
                                <tr class="table-secondary fw-bold">
                                    <td colspan="3" class="text-end">Grand Total</td>
                                    <td id="grand-total">Rp {{ number_format($total, 0, ',', '.') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    {{-- Pilihan alamat & ongkir --}}
                    <h5 class="fw-bold mb-2">Alamat & Ongkir</h5>
                    <div class="row g-2 mb-3">
                        <div class="col-12 col-md-6">
                            <label>Provinsi</label>
                            <select id="province" class="form-control"></select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label>Kota / Kabupaten</label>
                            <select id="city" class="form-control"></select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label>Kurir</label>
                            <select id="courier" class="form-control">
                                <option value="jne">JNE</option>
                                <option value="tiki">TIKI</option>
                                <option value="pos">POS</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-6 d-flex align-items-end">
                            <button type="button" id="cek-ongkir" class="btn btn-outline-primary w-100">Cek Ongkir</button>
                        </div>

                        <div class="col-12 mt-3" id="ongkir-results"></div>
                    </div>

                    {{-- Metode Pembayaran --}}
                    <div class="row g-2 mb-3">
                        <div class="col-12 col-md-6">
                            <button type="button" class="btn btn-outline-dark w-100 py-2" id="cod-button">COD</button>
                        </div>
                        <div class="col-12 col-md-6">
                            <button type="button" class="btn btn-primary w-100 py-2" id="pay-button">Bayar Sekarang</button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
// Pastikan ORIGIN_CITY_ID sudah ada di .env
const ORIGIN_CITY = '{{ env("ORIGIN_CITY_ID", "ORIGIN_CITY_ID") }}'; 
const totalBarang = '{{ (int) $transaksi->total_harga }}'; // Ambil dari $transaksi

$(document).ready(function() {
    // URL API RajaOngkir (atau API custom Anda)
    const provincesUrl = '/provinces';
    const citiesUrl = '/cities/';
    const ongkirCostUrl = '/ongkir/cost';
    
    // --- Inisialisasi Alamat ---
    // Load provinsi
    $.get(provincesUrl, function(data) {
        $('#province').append('<option value="">Pilih Provinsi</option>');
        if (data.data) { // Cek jika format data dibungkus oleh 'data'
             data = data.data; 
        }
        data.forEach(function(p) {
            $('#province').append('<option value="'+p.province_id+'">'+p.province+'</option>');
        });
    });

    // Load kota saat province berubah
    $('#province').on('change', function() {
        const provinceId = $(this).val();
        $('#city').html('');
        if (!provinceId) return;
        $.get(citiesUrl + provinceId, function(data) {
             if (data.data) { // Cek jika format data dibungkus oleh 'data'
                 data = data.data; 
             }
            $('#city').append('<option value="">Pilih Kota</option>');
            data.forEach(function(c) {
                // Pastikan key city_id/city_name sesuai dengan respons API Anda
                $('#city').append('<option value="'+c.city_id+'">'+c.type + ' ' + c.city_name+'</option>');
            });
        });
    });

    // --- Logika Ongkir ---
    $('#cek-ongkir').on('click', function() {
        const destination = $('#city').val();
        const courier = $('#courier').val();
        if (!destination) { alert('Pilih kota tujuan dulu'); return; }

        const weight = 1000; // Contoh berat. Sesuaikan dengan total berat barang Anda.
        
        $.ajax({
            url: ongkirCostUrl,
            method: 'POST',
            data: {
                origin: ORIGIN_CITY,
                destination: destination,
                weight: weight,
                courier: courier,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                // Asumsi response.data adalah array hasil cost
                const data = response.data || []; 
                let html = '';
                
                if (data.length === 0) {
                    html = '<div class="alert alert-warning">Tidak ada layanan ongkir ditemukan.</div>';
                } else {
                    html = '<div class="list-group">';
                    // Asumsi data.costs berisi array layanan/harga
                    data.forEach(function(svc) {
                        const service = svc.service;
                        const description = svc.description || '';
                        
                        svc.cost.forEach(function(costItem) {
                            const value = costItem.value;
                            const etd = costItem.etd || '-';
                            
                            html += '<label class="list-group-item d-flex justify-content-between align-items-center">';
                            html += '<div><input type="radio" name="ongkir_radio" value="'+value+'" data-service="'+service+'" data-kurir="'+courier+'"> ';
                            html += '<strong>'+service+'</strong> '+description+' <small>(est: '+etd+' hari)</small></div>';
                            html += '<div>Rp '+Number(value).toLocaleString('id-ID')+'</div>';
                            html += '</label>';
                        });
                    });
                    html += '</div>';
                }
                $('#ongkir-results').html(html);
            },
            error: function(err) {
                console.error(err);
                alert('Gagal mengambil data ongkir');
            }
        });
    });

    // Pilih layanan ongkir -> update total
    $(document).on('change', 'input[name="ongkir_radio"]', function() {
        const ongkir = parseInt($(this).val() || 0, 10);
        const kurir = $(this).data('kurir');
        const service = $(this).data('service');
        const totalBarangInt = parseInt(totalBarang, 10);

        $('#ongkir').val(ongkir);
        $('#kurir_input').val(kurir);
        $('#service_input').val(service);
        $('#alamat_pengiriman_input').val($('#user-address').val());

        $('#total-ongkir').text('Rp ' + ongkir.toLocaleString('id-ID'));
        const grand = totalBarangInt + ongkir;
        $('#grand-total').text('Rp ' + grand.toLocaleString('id-ID'));
    });

    // --- Logika Pembayaran ---

    // COD button = buka WA
    $('#cod-button').on('click', function() {
        // ... (kode WA tetap sama)
    });

    // Bayar Sekarang: Midtrans Snap
    $('#pay-button').on('click', function(e) {
        e.preventDefault();

        const ongkir = parseInt($('#ongkir').val() || 0, 10);
        const kurir = $('#kurir_input').val() || $('#courier').val();
        const service = $('#service_input').val() || '';
        const alamat = $('#user-address').val() || '';

        // Validasi minimal
        if (!alamat) { alert('Isi alamat pengiriman terlebih dahulu'); return; }
        if (ongkir === 0 && kurir !== '') { alert('Pilih atau cek ongkir terlebih dahulu.'); return; }

        // Panggil endpoint create snap token (MUNGKIN INI TIDAK PERLU JIKA SUDAH ADA SNAP TOKEN DI VIEW)
        // **CATATAN PENTING**: Jika Anda sudah me-render $snapToken dari Controller, Anda tidak perlu AJAX lagi.
        
        // Pilihan 1: Jika Midtrans Token sudah ada di Blade (dari controller beliSekarang)
        const snapToken = '{{ $snapToken }}';
        
        if (snapToken) {
            snap.pay(snapToken, {
                onSuccess: function(result) {
                    $('#payment-result').val(JSON.stringify(result));
                    // Lakukan update data transaksi sebelum submit
                    $('#payment-form').submit();
                },
                onPending: function(result) {
                    $('#payment-result').val(JSON.stringify(result));
                    // Lakukan update data transaksi sebelum submit
                    $('#payment-form').submit();
                },
                onError: function(result) {
                    alert('Pembayaran gagal. Coba lagi.');
                    console.error(result);
                }
            });
        } 
        // Pilihan 2: Jika Anda membuat Snap Token via AJAX (tidak disarankan untuk flow "Beli Sekarang" ini)
        // Jika Anda tetap ingin menggunakan AJAX (seperti kode Anda sebelumnya), 
        // pastikan route 'create.snap' sudah ada dan menerima parameter yang tepat.
    });
});
</script>
</body>
</html>