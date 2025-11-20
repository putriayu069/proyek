<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Keranjang Saya</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/profile.css') }}">
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    <style>
        .selected-item {
            background-color: #f0f8ff;
            border-left: 4px solid #4285f4;
        }
        .select-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 2px solid #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            margin-right: 15px;
            transition: all 0.3s;
        }
        .select-btn.selected {
            background-color: #4285f4;
            color: white;
            border-color: #4285f4;
        }
        .checkout-btn {
            padding: 10px 30px;
            font-size: 1.1em;
            font-weight: bold;
            background-color: #4285f4;
            color: white;
            border: none;
            border-radius: 8px;
        }
        .checkout-btn:hover {
            background-color: #3367d6;
        }
        .checkout-btn:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }
        .item-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 5px;
        }
        .item-name {
            font-size: 1.1rem;
            font-weight: 600;
        }
        .item-price {
            color: #28a745;
            font-weight: bold;
        }
        .item-price-discount {
            color: #dc3545;
            font-weight: bold;
        }
        .total-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #dee2e6;
        }
    </style>
</head>
<body>
    {{-- Navbar --}}
    <nav class="navbar navbar-expand-lg navbar-custom px-4">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="{{ url('/') }}">
                <img src="{{ asset('images/logo.jpg') }}" alt="E-Mebel Logo" style="height: 45px;" class="me-2">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item"><a class="nav-link" href="{{ url('dashboard') }}">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('profile') }}">Profil</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('riwayat.pesanan') }}">Riwayat Pesanan</a></li>
                </ul>

                <a class="nav-link" href="{{ route('keranjang') }}">
                    <img src="{{ asset('images/keranjang.png') }}" alt="Keranjang" style="width: 37px; height: 23px;">
                </a>

                @auth
                <a href="{{ route('logout') }}" class="nav-link d-flex align-items-center ms-2" title="Logout">
                    <img src="{{ asset('images/logout.png') }}" alt="Logout" style="width: 20px; height: 20px;">
                </a>
                @endauth
            </div>
        </div>
    </nav>

    {{-- Keranjang --}}
    <section class="keranjang-wrapper bg-light py-4">
        <div class="container">
            <h4 class="mb-3 fw-bold">Keranjang Saya</h4>

            @php 
                $cart = session('cart', []); 
            @endphp

            @if(count($cart) === 0)
                <div class="alert alert-info">Keranjang Anda kosong.</div>
            @else
                @foreach($cart as $id => $item)
                   @php
                        $barang = \App\Models\Barang::with('vouchers')->find($id);
                        if (!$barang) continue;

                        $hargaAsli = $barang->harga;

                        $voucherAktif = $barang->vouchers
                            ->filter(fn($v) =>
                                $v->aktif &&
                                (!$v->masa_berlaku || \Carbon\Carbon::now()->lte(\Carbon\Carbon::parse($v->masa_berlaku))) &&
                                (!$v->batas_penggunaan || $v->jumlah_digunakan < $v->batas_penggunaan)
                            )
                            ->sortByDesc('diskon')
                            ->first();

                        $jumlah = $item['jumlah'] ?? 1;
                        $hargaDiskon = $hargaAsli;
                        $voucherText = '';
                        $subtotalDiskon = $hargaAsli * $jumlah;

                        if ($voucherAktif) {
                            $voucherText = "(-{$voucherAktif->diskon}%)";

                            // Hitung jumlah barang yang bisa diskon sesuai batas penggunaan
                            $eligibleQty = $jumlah;
                            if ($voucherAktif->batas_penggunaan) {
                                $sisaVoucher = $voucherAktif->batas_penggunaan - $voucherAktif->jumlah_digunakan;
                                $eligibleQty = min($jumlah, max(0, $sisaVoucher));
                            }

                            $hargaDiskon = $hargaAsli * (1 - $voucherAktif->diskon / 100);
                            $subtotalDiskon = $hargaDiskon * $eligibleQty + $hargaAsli * ($jumlah - $eligibleQty);
                        }

                        $subtotalAsli = $hargaAsli * $jumlah;
                        $stokTerbaru = $barang->stok;
                    @endphp
                    <div class="d-flex align-items-center border-bottom py-3 justify-content-between item-container" 
                    data-item-id="{{ $id }}" 
                    data-subtotal-asli="{{ $subtotalAsli }}"
                    data-subtotal-diskon="{{ $subtotalDiskon }}"
                    data-stok="{{ $stokTerbaru }}">

                    <div class="d-flex align-items-center">
                        <div class="select-btn" onclick="toggleSelectItem(this, '{{ $id }}')">
                            <i class="bi bi-check-lg"></i>
                        </div>
                        <img src="{{ asset('storage/' . $item['gambar']) }}" alt="{{ $item['nama'] }}" class="item-image me-3">
                        <div>
                            <div class="item-name">{{ $item['nama'] }}</div>

                            @if($voucherAktif && $stokTerbaru > 0)
                                <div class="text-muted" style="text-decoration: line-through;">
                                    Rp. {{ number_format($hargaAsli, 0, ',', '.') }}
                                </div>
                                <div class="item-price-discount">
                                    Rp. {{ number_format($hargaDiskon, 0, ',', '.') }} {{ $voucherText }}
                                </div>
                            @else
                                <div class="item-price">
                                    Rp. {{ number_format($hargaAsli, 0, ',', '.') }}
                                </div>
                            @endif

                            <div class="text-muted">Subtotal: Rp. {{ number_format($stokTerbaru > 0 ? $subtotalDiskon : 0, 0, ',', '.') }}</div>

                            <form action="{{ route('keranjang.update') }}" method="POST" class="d-flex align-items-center mt-2">
                                @csrf
                                <input type="hidden" name="barang_id" value="{{ $id }}">
                                <button type="submit" name="action" value="decrease" class="btn btn-outline-secondary btn-sm me-2" @if($stokTerbaru <= 0) disabled @endif>-</button>
                                <span class="mx-2">{{ $stokTerbaru > 0 ? $jumlah : 0 }}</span>
                                <button type="submit" name="action" value="increase" class="btn btn-outline-secondary btn-sm" @if($stokTerbaru <= 0) disabled @endif>+</button>
                            </form>

                            @if($stokTerbaru <= 0)
                                <span class="text-danger fw-bold mt-2">
                                    <i class="bi bi-exclamation-triangle-fill"></i> Stok Habis
                                </span>
                            @endif
                        </div>
                    </div>

                    <form action="{{ route('keranjang.hapus') }}" method="POST" 
                        onsubmit="return confirm('Yakin ingin menghapus barang ini dari keranjang?');" 
                        class="ms-auto">
                        @csrf
                        <input type="hidden" name="barang_id" value="{{ $id }}">
                        <button class="btn btn-danger btn-sm">Hapus</button>
                    </form>
                </div>
                @endforeach

                {{-- Input Voucher Promo --}}
                <div class="mt-4" id="promo-section" style="display: none;">
                    <form action="{{ route('keranjang.applyPromo') }}" method="POST" id="promo-form">
                        @csrf
                        <div class="input-group mb-3">
                            <input type="text" name="promo_code" class="form-control"
                                   placeholder="Masukkan kode promo"
                                   value="{{ session('promo_code') }}">
                            <button class="btn btn-primary" type="submit">Gunakan Promo</button>
                        </div>
                        <div id="promo-selected-items-container"></div>
                    </form>

                    {{-- Jika promo sudah diterapkan, tampilkan opsi hapus --}}
                    @if(session('promo'))
                        <form action="{{ route('keranjang.removePromo') }}" method="POST" class="mt-2">
                            @csrf
                            <button class="btn btn-outline-danger btn-sm" type="submit">Hapus Promo</button>
                        </form>
                    @endif

                    {{-- Pesan Promo --}}
                    @if(session('promo_success'))
                        <div class="alert alert-success">{{ session('promo_success') }}</div>
                    @endif
                    @if(session('promo_error'))
                        <div class="alert alert-danger">{{ session('promo_error') }}</div>
                    @endif
                </div>

                {{-- Total Belanja --}}
                <div class="total-section mt-4" id="total-section" style="display: none;">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Belanja (Sebelum Diskon):</span>
                        <span class="fw-bold" id="subtotal-asli-display">Rp. 0</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Diskon Voucher:</span>
                        <span class="fw-bold text-success" id="voucher-discount-display">- Rp. 0</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal Setelah Voucher:</span>
                        <span class="fw-bold" id="subtotal-display">Rp. 0</span>
                    </div>
                    
                    <div id="discount-row" style="display: none;">
                        <div class="d-flex justify-content-between mb-2 text-success">
                            <span>Diskon Promo (<span id="promo-code-display"></span>):</span>
                            <span class="fw-bold" id="discount-display">- Rp. 0</span>
                        </div>
                        <hr>
                    </div>

                    <div class="d-flex justify-content-between">
                        <span class="h5 mb-0">Total Akhir:</span>
                        <span class="h5 mb-0 text-success fw-bold" id="grand-total">Rp. 0</span>
                    </div>
                </div>

                {{-- Checkout --}}
                <form action="{{ route('pembayaran') }}" method="POST" id="checkout-form" class="mt-3">
                    @csrf
                    <div id="selected-items-container"></div>
                    <input type="hidden" name="applied_promo" id="applied-promo-input" value="{{ session('promo_code') }}">
                    <button type="button" id="checkout-button" onclick="submitCheckout()" class="checkout-btn" disabled>
                        Checkout (<span id="selected-count">0</span> item)
                    </button>
                </form>
            @endif
        </div>
    </section>

    {{-- JS --}}
    <script>
        let selectedItems = [];
        let promoData = @json(session('promo', null));

        // Load selectedItems from localStorage on page load
        window.onload = function() {
            const stored = localStorage.getItem('selectedItems');
            if (stored) {
                selectedItems = JSON.parse(stored);
                // Apply selection to existing items
                selectedItems.forEach(itemId => {
                    const itemContainer = document.querySelector(`[data-item-id="${itemId}"]`);
                    if (itemContainer) {
                        const button = itemContainer.querySelector('.select-btn');
                        if (button) {
                            button.classList.add('selected');
                            itemContainer.classList.add('selected-item');
                        }
                    }
                });
                updateCheckoutButton();
                updateTotals();
                updatePromoForm();
            }
        };

        function toggleSelectItem(button, itemId) {
            const itemContainer = button.closest('.item-container');
            const isSelected = button.classList.contains('selected');
            
            if (isSelected) {
                button.classList.remove('selected');
                itemContainer.classList.remove('selected-item');
                selectedItems = selectedItems.filter(id => id !== itemId);
            } else {
                button.classList.add('selected');
                itemContainer.classList.add('selected-item');
                selectedItems.push(itemId);
            }
            
            // Save to localStorage
            localStorage.setItem('selectedItems', JSON.stringify(selectedItems));
            
            updateCheckoutButton();
            updateTotals();
            updatePromoForm();
        }

        function updateCheckoutButton() {
            const checkoutButton = document.getElementById('checkout-button');
            const selectedCount = document.getElementById('selected-count');
            const promoSection = document.getElementById('promo-section');
            const totalSection = document.getElementById('total-section');
            
            selectedCount.textContent = selectedItems.length;
            checkoutButton.disabled = selectedItems.length === 0;

            // Tampilkan section promo dan total hanya jika ada item yang dipilih
            if (selectedItems.length > 0) {
                promoSection.style.display = 'block';
                totalSection.style.display = 'block';
            } else {
                promoSection.style.display = 'none';
                totalSection.style.display = 'none';
            }

            const container = document.getElementById('selected-items-container');
            container.innerHTML = '';
            selectedItems.forEach(itemId => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'produk[]';
                input.value = itemId;
                container.appendChild(input);
            });
        }

        function updateTotals() {
    let subtotalAsli = 0;
    let subtotalDiskon = 0;
    
    selectedItems.forEach(itemId => {
        const itemContainer = document.querySelector(`[data-item-id="${itemId}"]`);
        if (itemContainer) {
            const itemSubtotalAsli = parseFloat(itemContainer.getAttribute('data-subtotal-asli')) || 0;
            const itemSubtotalDiskon = parseFloat(itemContainer.getAttribute('data-subtotal-diskon')) || 0;
            const stok = parseInt(itemContainer.getAttribute('data-stok')) || 0;

            subtotalAsli += stok > 0 ? itemSubtotalAsli : 0;
            subtotalDiskon += stok > 0 ? itemSubtotalDiskon : 0;
        }
    });

    const voucherDiscount = subtotalAsli - subtotalDiskon;

    document.getElementById('subtotal-asli-display').textContent = "Rp. " + subtotalAsli.toLocaleString('id-ID');
    document.getElementById('voucher-discount-display').textContent = "- Rp. " + voucherDiscount.toLocaleString('id-ID');
    document.getElementById('subtotal-display').textContent = "Rp. " + subtotalDiskon.toLocaleString('id-ID');

    let promoDiscount = 0;
    let finalTotal = subtotalDiskon;

    if (promoData) {
        if (promoData.percent > 0) {
            promoDiscount = subtotalDiskon * (promoData.percent / 100);
        } else if (promoData.amount > 0) {
            promoDiscount = Math.min(promoData.amount, subtotalDiskon);
        }
        finalTotal = subtotalDiskon - promoDiscount;

        document.getElementById('discount-row').style.display = 'block';
        document.getElementById('promo-code-display').textContent = promoData.kode || '';
        document.getElementById('discount-display').textContent = "- Rp. " + promoDiscount.toLocaleString('id-ID');
    } else {
        document.getElementById('discount-row').style.display = 'none';
    }

    document.getElementById('grand-total').textContent = "Rp. " + finalTotal.toLocaleString('id-ID');
}

        function updatePromoForm() {
            const container = document.getElementById('promo-selected-items-container');
            container.innerHTML = '';
            selectedItems.forEach(itemId => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'produk[]';
                input.value = itemId;
                container.appendChild(input);
            });
        }

        function submitCheckout() {
            if (selectedItems.length > 0) {
                document.getElementById('checkout-form').submit();
            }
        }

        // Load promo data dari session
        @if(session('promo'))
            promoData = {
                kode: '{{ session('promo')['kode'] ?? '' }}',
                percent: {{ session('promo')['percent'] ?? 0 }},
                amount: {{ session('promo')['amount'] ?? 0 }},
                min_pembelian: {{ session('promo')['min_pembelian'] ?? 0 }}
            };
        @endif
    </script>
</body>
</html>
