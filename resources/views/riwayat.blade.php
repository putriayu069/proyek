<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'E-Mebel') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body>
<div class="d-flex flex-column min-vh-100">

    <nav class="navbar navbar-expand-lg navbar-custom px-4">
    <div class="container">
         <a class="navbar-brand d-flex align-items-center" href="{{ url('/') }}">
            <img src="{{ asset('images/logo.jpg') }}" 
                 alt="E-Mebel Logo" 
                 style="height: 45px;" 
                 class="me-2">
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


    <main class="container mt-5">
        <h1 class="text-center mb-4">Riwayat Transaksi</h1>

        @if($orders->isEmpty())
            <div class="alert alert-warning text-center">
                Belum ada transaksi yang dilakukan.
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-bordered align-middle text-center">
                  <thead class="table-dark">
    <tr>
        <th>ID Transaksi</th>
        <th>Nama Barang</th>
        <th>Harga Barang</th>
        <th>Total Bayar</th>
        <th>Status</th>
        <th>Tanggal</th>
    </tr>
</thead>
<tbody>
    @foreach($orders as $order)
        <tr>
            <td>{{ $order->id }}</td>
            <td>{{ $order->nama_barang }}</td>
            <td>Rp {{ number_format($order->harga_asli, 0, ',', '.') }}</td>

            <td class="fw-bold text-success">
                Rp {{ number_format($order->total_bayar, 0, ',', '.') }}
            </td>
            <td>
                <span class="badge bg-success">Selesai</span>
            </td>
            <td>{{ $order->created_at->format('d-m-Y H:i') }}</td>
        </tr>
    @endforeach
</tbody>


                </table>
            </div>
        @endif
    </main>

    <footer class="bg-dark text-white text-center py-3 mt-auto">
        &copy; {{ date('Y') }} E-Mebel. All Rights Reserved.
    </footer>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
