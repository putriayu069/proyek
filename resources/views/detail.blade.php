<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $barang->nama }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; font-family: 'Arial', sans-serif; }
        .navbar-custom { background-color: #ffffff; box-shadow: 0 2px 4px #0000000d; }
        .navbar-custom .navbar-brand img { height: 40px; }
        .navbar-custom .nav-link { color: #333; font-weight: 500; }
        .navbar-custom .nav-link:hover { color: #deeb26; }
        .product-image { border-radius: 8px; transition: transform 0.3s ease, box-shadow 0.3s ease; }
        .product-image:hover { transform: scale(1.05) rotate(1deg); box-shadow: 0px 12px 20px rgba(0,0,0,0.3); }
        .btn-custom { background-color: #69a5ff; color: white; border-radius: 5px; transition: 0.3s ease; }
        .btn-custom:hover { background-color: #0a58ca; transform: translateY(-2px); }
        .btn-primary { background-color: #198754; color: white; border-radius: 5px; transition: 0.3s ease; }
        .btn-primary:hover { background-color: #157347; transform: translateY(-2px); }
        .product-info { animation: fadeInUp 0.8s ease; }
        @keyframes fadeInUp { from {opacity:0; transform:translateY(20px);} to {opacity:1; transform:translateY(0);} }
    </style>
</head>
<body>

{{-- Navbar --}}
<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-custom px-4">
    <div class="container">
        <a class="navbar-brand" href="{{ url('/') }}">
            <img src="{{ asset('images/logo.jpg') }}" alt="E-Mebel Logo">
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
            <a href="{{ route('logout') }}" 
               class="nav-link d-flex align-items-center ms-2" 
               title="Logout">
                <img src="{{ asset('images/logout.png') }}" 
                     alt="Logout" 
                     style="width: 20px; height: 20px;">
            </a>
            @endauth
        </div>
    </div>
</nav>

{{-- Product Detail --}}
<section class="product-wrapper py-5">
    <div class="container">
        @if(session('error'))
            <div class="alert alert-danger animate__animated animate__flash mb-4">
                <strong>Peringatan Debug:</strong> {{ session('error') }}
            </div>
        @endif
        <div class="row align-items-center animate__animated animate__fadeIn">

            @php
                // Ambil voucher aktif berdasarkan masa berlaku dan batas penggunaan
                $voucherAktif = $barang->vouchers
                    ->filter(fn($v) =>
                        $v->aktif &&
                        (!$v->masa_berlaku || \Carbon\Carbon::now()->lte(\Carbon\Carbon::parse($v->masa_berlaku))) &&
                        (!$v->batas_penggunaan || $v->jumlah_digunakan < $v->batas_penggunaan)
                    )
                    ->first();

                $hargaAwal = $barang->harga;
                $hargaAkhir = $voucherAktif
                    ? $hargaAwal - ($hargaAwal * $voucherAktif->diskon / 100)
                    : $hargaAwal;
            @endphp

            <div class="col-md-6 mb-4 mb-md-0 position-relative">
                {{-- Label Diskon --}}
                @if($voucherAktif)
                    <span class="position-absolute top-0 start-0 bg-danger text-white px-2 py-1"
                          style="border-bottom-right-radius:4px; font-size:0.85rem; z-index:10;">
                        Diskon {{ $voucherAktif->diskon }}%
                    </span>
                @endif

                <img src="{{ asset('storage/' . $barang->gambar) }}"
                     alt="{{ $barang->nama }}"
                     class="product-image w-100 shadow-sm">
            </div>

            <div class="col-md-6 product-info">
                <h2 style="font-size:2.1rem;">{{ $barang->nama }}</h2>

                {{-- Harga --}}
                @if($voucherAktif)
                    <p class="text-muted mb-1" style="font-size:0.9rem; text-decoration:line-through;">
                        Rp {{ number_format($hargaAwal, 0, ',', '.') }}
                    </p>
                @endif
                <div class="price mb-2 text-success fw-bold" style="font-size:1.5rem;">
                    Rp {{ number_format($hargaAkhir, 0, ',', '.') }}
                </div>

                <p><strong>Stok:</strong> {{ $barang->stok }}</p>
                <p><strong>Merek:</strong> {{ $barang->merek }}</p>
                <p class="mt-3">{!! nl2br(e($barang->deskripsi)) !!}</p>

                <div class="d-flex gap-2 mt-4">
                    {{-- Tambah ke Keranjang --}}
                    <form action="{{ route('keranjang.tambah') }}" method="POST">
                        @csrf
                        <input type="hidden" name="barang_id" value="{{ $barang->id }}">
                        <button class="btn btn-custom animate__animated animate__bounceIn" type="submit">
                            + Keranjang
                        </button>
                    </form>

                    {{-- Beli Sekarang --}}
                    <form action="{{ route('beli.sekarang') }}" method="POST">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $barang->id }}">
                        <input type="hidden" name="harga" value="{{ $hargaAkhir }}">
                        <button type="submit" class="btn btn-primary animate__animated animate__bounceIn">
                            Beli Sekarang
                        </button>
                    </form>

                    {{-- Chat --}}
                    <a href="{{ route('chat') }}" class="btn btn-warning btn-sm d-flex align-items-center gap-1">
                        <i class="bi bi-chat-dots"></i> Chat
                    </a>
                </div>

            </div>
        </div>
    </div>
</section>

{{-- Footer --}}
<footer class="bg-dark text-white text-center py-3 mt-4">
    &copy; {{ date('Y') }} E-Mebel. All Rights Reserved.
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
