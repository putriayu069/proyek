<!DOCTYPE html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Emebel</title>
    <script src="https://cdn.tailwindcss.com/3.4.16"></script>
   <script src="https://cdn.tailwindcss.com/3.4.16"></script>
    <script>
      tailwind.config = {
        theme: {
          extend: {
            colors: { primary: "#4318FF", secondary: "#4FD1C5" },
            borderRadius: {
              none: "0px",
              sm: "4px",
              DEFAULT: "8px",
              md: "12px",
              lg: "16px",
              xl: "20px",
              "2xl": "24px",
              "3xl": "32px",
              full: "9999px",
              button: "8px",
            },
          },
        },
      };
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap"
      rel="stylesheet"
    />
    <link
      href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css"
      rel="stylesheet"
    />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/echarts/5.5.0/echarts.min.js"></script>
    <style>
      :where([class^="ri-"])::before { content: "\f3c2"; }
      input[type="number"]::-webkit-inner-spin-button,
      input[type="number"]::-webkit-outer-spin-button {
          -webkit-appearance: none;
          margin: 0;
      }
      body {
          font-family: 'Inter', sans-serif;
      }
    </style>
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<div x-data="{ sidebarOpen: false }" class="flex flex-col md:flex-row min-h-screen bg-gray-50">

<!-- Mobile Header -->
<div class="flex items-center justify-between p-4 bg-white shadow md:hidden">
    <h1 class="text-xl font-bold text-primary">EMebel</h1>
    <button @click="sidebarOpen = !sidebarOpen" class="text-gray-700 focus:outline-none">
        <i class="ri-menu-line text-2xl"></i>
    </button>
</div>

<aside :class="sidebarOpen ? 'block' : 'hidden'" class="md:block w-full md:w-64 bg-[#111827] text-white flex flex-col z-20 md:z-auto fixed md:relative md:top-0 md:left-0 top-0 left-0 h-full md:h-auto overflow-y-auto md:overflow-visible">

    <div class="p-6 flex items-center">
        <div class="w-8 h-8 flex items-center justify-center bg-primary rounded-md mr-2">
            <i class="ri-dashboard-line text-white"></i>
        </div>
        <h1 class="text-xl font-bold">EMebel</h1>
    </div>

    <!-- Menu -->
    <div class="flex-1 flex flex-col overflow-y-auto">
        <div class="px-4 py-2">
            <p class="text-xs text-gray-400 font-medium mb-2">ADMIN MENU</p>
            <ul class="space-y-1">
                <!-- Dashboard -->
                <li>
                    <a href="{{ route('admin.dashboard') }}" 
                    class="flex items-center px-4 py-2 rounded-md {{ request()->routeIs('admin.dashboard') ? 'text-white bg-primary/10' : 'text-gray-400 hover:bg-white/5' }}">
                        <div class="w-5 h-5 flex items-center justify-center mr-3">
                            <i class="ri-dashboard-line"></i>
                        </div>
                        <span>Dashboard</span>
                    </a>
                </li>

                <!-- Produk (submenu) -->
                <li x-data="{ open: {{ request()->routeIs('admin.barang.index') || request()->routeIs('admin.barang.create') ? 'true' : 'false' }} }">
                    <button @click="open = !open" 
                            class="flex justify-between items-center w-full px-4 py-2 text-white hover:bg-white/5 rounded-md">
                        <div class="flex items-center">
                            <i class="ri-product-hunt-line mr-3"></i>
                            <span>Produk</span>
                        </div>
                        <i class="ri-arrow-down-s-line" :class="{ 'rotate-180': open }"></i>
                    </button>
                    <ul x-show="open" x-cloak class="ml-6 mt-2 space-y-1">
                        <li>
                            <a href="{{ route('admin.barang.index') }}" 
                            class="flex items-center px-4 py-1 rounded-md {{ request()->routeIs('admin.barang.index') ? 'text-white bg-primary/10' : 'text-gray-300 hover:bg-white/5' }}">
                                <span>Data Barang</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.barang.create') }}" 
                            class="flex items-center px-4 py-1 rounded-md {{ request()->routeIs('admin.barang.create') ? 'text-white bg-primary/10' : 'text-gray-300 hover:bg-white/5' }}">
                                <span>Tambah Barang</span>
                            </a>
                        </li>
                    </ul>
                </li>


                <!-- Transaksi Menu -->
                <li>
                    <a href="{{ route('admin.transaksi.index') }}"
                        class="flex items-center px-4 py-2 rounded-md {{ request()->routeIs('admin.transaksi.index') ? 'text-white bg-primary/10' : 'text-gray-400 hover:bg-white/5' }}">
                        <i class="ri-order-play-line mr-3"></i>
                        <span>Transaksi</span>
                    </a>
                </li>
            <li>
    <a href="{{ route('admin.manual') }}" 
       class="flex items-center px-4 py-2 rounded-md {{ request()->routeIs('admin.manual') ? 'text-white bg-primary/10' : 'text-gray-400 hover:bg-white/5' }}">
        <div class="w-5 h-5 flex items-center justify-center mr-3">
            <i class="ri-edit-box-line"></i>
        </div>
        <span>Manual Transaksi</span>
    </a>
</li>
 <li>
   <a href="{{ route('admin.chat') }}" 
   class="flex items-center px-4 py-2 rounded-md {{ request()->routeIs('admin.chat') ? 'text-white bg-primary/10' : 'text-gray-400 hover:bg-white/5' }}">
    <div class="w-5 h-5 flex items-center justify-center mr-3">
        <i class="ri-chat-1-line"></i>
    </div>
    <span>Chat</span>
</a>
</li>
</ul>
</div>

        <!-- Report Section -->
        <!-- Log Out Section -->
        <div class="px-4 py-2 mt-auto">
            <form action="{{ route('logout') }}" method="GET">
                @csrf
                <button type="submit"
                    class="flex items-center w-full px-4 py-2 rounded-md text-gray-400 hover:bg-white/5">
                    <i class="ri-logout-circle-r-line mr-3"></i>
                    <span>Log Out</span>
                </button>
            </form>
        </div>
    </div>
</aside>


      <!-- Main Content Area -->
    <main class="p-4 md:p-6">
            @yield('content') {{-- Tempat konten halaman dashboard kamu --}}
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-4 md:mb-6">
          <!-- Profit Card -->
          <div class="bg-white rounded-lg p-4 md:p-6 shadow-sm">
            <div class="flex justify-between items-center mb-4">
              <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                <i class="ri-shopping-cart-line text-primary"></i>
              </div>
            </div>
            <h3 class="text-2xl font-bold text-gray-900">
              Rp{{ number_format($totalSemuaPembayaran, 0, ',', '.') }}
            </h3>
            <div class="flex items-center justify-between">
              <p class="text-sm text-gray-500">Pemasukan</p>
              <span class="text-xs font-medium text-green-500 flex items-center">
                4,35% <i class="ri-arrow-up-line ml-1"></i>
              </span>
            </div>
          </div>
          <!-- Product Card -->
          <div class="bg-white rounded-lg p-4 md:p-6 shadow-sm">
            <div class="flex justify-between items-center mb-4">
              <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                <i class="ri-archive-line text-primary"></i>
              </div>
            </div>
            <h3 class="text-2xl font-bold text-gray-900">{{ $jumlahBarang }} Barang</h3>
            <p class="text-md text-gray-700 mt-1">Total Stok: {{ number_format($totalStok) }}</p>
            <div class="flex items-center justify-between mt-2">
              <p class="text-sm text-gray-500">Statistik Barang</p>
              <span class="text-xs font-medium text-green-500 flex items-center">
                2,59% <i class="ri-arrow-up-line ml-1"></i>
              </span>
            </div>
          </div>
          <!-- Users Card -->
          <div class="bg-white rounded-lg p-4 md:p-6 shadow-sm">
            <div class="flex justify-between items-center mb-4">
              <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                <i class="ri-user-line text-primary"></i>
              </div>
            </div>
            <h3 class="text-2xl font-bold text-gray-900">{{ $jumlahPengguna }}</h3>
            <div class="flex items-center justify-between">
              <p class="text-sm text-gray-500">Total Pengguna</p>
              <span class="text-xs font-medium text-green-500 flex items-center">
                0,95% <i class="ri-arrow-up-line ml-1"></i>
              </span>
            </div>
          </div>
        </div>


      <!-- Tambah Voucher Diskon -->
<!-- Tambah Voucher Diskon -->
<div class="bg-white rounded-lg p-4 md:p-6 shadow-sm mt-6">
    <h2 class="text-lg font-semibold mb-4">Tambah Voucher Diskon</h2>

    @if(session('success'))
        <div class="p-2 mb-3 bg-green-100 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if(!empty($notifikasiStokHabis))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
        <strong class="font-bold">⚠️ Stok Habis!</strong>
        <span class="block sm:inline">{{ $notifikasiStokHabis }}</span>
    </div>
@endif

    <form action="{{ route('admin.voucher.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @csrf

        <!-- Kode Voucher -->
        <div>
            <label class="block text-sm font-medium mb-1">Kode Voucher</label>
            <input type="text" name="kode" class="w-full border rounded px-3 py-2" required>
        </div>

        <!-- Diskon -->
        <div>
            <label class="block text-sm font-medium mb-1">Diskon (%)</label>
            <input type="number" name="diskon" class="w-full border rounded px-3 py-2" min="1" max="100" required>
        </div>

        <!-- Tanggal Mulai -->
        <div>
            <label class="block text-sm font-medium mb-1">Tanggal Mulai</label>
            <input type="date" name="tanggal_mulai" class="w-full border rounded px-3 py-2">
        </div>

        <!-- Tanggal Berakhir -->
        <div>
            <label class="block text-sm font-medium mb-1">Tanggal Berakhir</label>
            <input type="date" name="tanggal_berakhir" class="w-full border rounded px-3 py-2">
        </div>

        <!-- Batas Penggunaan -->
        <div>
            <label class="block text-sm font-medium mb-1">Batas Penggunaan</label>
            <input type="number" name="batas_penggunaan" class="w-full border rounded px-3 py-2" min="1" placeholder="Contoh: 100">
        </div>

        <!-- Pilih Produk -->
        <div class="md:col-span-2">
            <label class="block text-sm font-medium mb-1">Pilih Produk (Voucher Berlaku)</label>
            <select name="barang_ids[]" multiple class="w-full border rounded px-3 py-2 h-40">
               <div id="notifStokHabis" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mt-2">
                <strong class="font-bold">⚠️ Stok Habis!</strong>
                <span id="listBarangHabis" class="block sm:inline"></span>
            </div>

                @foreach($barangs as $barang)
                   <option value="{{ $barang->id }}" data-stok="{{ $barang->stok }}">
                    {{ $barang->nama }} (Stok: {{ $barang->stok }})
                </option>

                @endforeach
            </select>
            <p class="text-xs text-gray-500 mt-1">Tekan Ctrl (Windows) / Cmd (Mac) untuk memilih lebih dari satu produk.</p>
        </div>

        <!-- Tombol Simpan -->
        <div class="md:col-span-2 flex justify-end">
            <button type="submit" class="px-4 py-2 bg-primary text-white rounded">Simpan</button>
        </div>
    </form>
</div>

<!-- Daftar Voucher -->
<!-- Daftar Voucher -->
<div class="bg-white rounded-lg p-4 md:p-6 shadow-sm mt-6">
    <h2 class="text-lg font-semibold mb-4">Daftar Voucher</h2>

    <table class="w-full border-collapse">
        <thead>
            <tr class="bg-gray-100 text-left">
                <th class="p-2 border">Kode</th>
                <th class="p-2 border">Diskon</th>
                <th class="p-2 border">Masa Berlaku</th>
                <th class="p-2 border">Batas Penggunaan</th>
                <th class="p-2 border">Status</th>
                <th class="p-2 border">Produk Berlaku</th>
                <th class="p-2 border">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($vouchers as $voucher)
            <tr>
                <td class="p-2 border font-semibold">{{ $voucher->kode }}</td>
                <td class="p-2 border">{{ $voucher->diskon }}%</td>
                <td class="p-2 border">
                    @if($voucher->tanggal_mulai && $voucher->tanggal_berakhir)
                        {{ $voucher->tanggal_mulai }} s.d {{ $voucher->tanggal_berakhir }}
                    @else
                        <span class="text-gray-400 text-sm">Tidak ditentukan</span>
                    @endif
                </td>
                <td class="p-2 border">
                    @if($voucher->batas_penggunaan)
                        {{ $voucher->jumlah_digunakan ?? 0 }} / {{ $voucher->batas_penggunaan }}
                    @else
                        <span class="text-gray-400 text-sm">Tanpa batas</span>
                    @endif
                </td>

                <!-- Status -->
                <td class="p-2 border">
                    @if($voucher->status === 'Habis')
                        <span class="px-2 py-1 bg-red-100 text-red-600 rounded text-xs">Habis</span>
                    @elseif($voucher->status === 'Kadaluarsa')
                        <span class="px-2 py-1 bg-yellow-100 text-yellow-600 rounded text-xs">Kadaluarsa</span>
                    @else
                        <span class="px-2 py-1 bg-green-100 text-green-600 rounded text-xs">Aktif</span>
                    @endif
                </td>

                <td class="p-2 border">
                    @foreach($voucher->barangs as $barang)
                        <span class="px-2 py-1 bg-blue-100 text-blue-600 rounded text-xs">{{ $barang->nama }}</span>
                    @endforeach
                </td>

                <td class="p-2 border">
                    <form action="{{ route('admin.voucher.destroy', $voucher->id) }}" 
                          method="POST" class="inline-block"
                          onsubmit="return confirm('Yakin ingin menghapus voucher ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-3 py-1 bg-red-600 text-white rounded">Hapus</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Tambah Promo -->
<div class="bg-white rounded-lg p-6 shadow-sm mt-6">
    <h2 class="text-lg font-semibold mb-4">Tambah Promo</h2>

    <!-- Pesan sukses -->
    @if(session('success'))
        <div class="p-2 mb-3 bg-green-100 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    <!-- Error -->
    @if ($errors->any())
        <div class="p-2 mb-3 bg-red-100 text-red-700 rounded">
            <ul class="list-disc list-inside mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.promo.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @csrf

        <!-- Kode Promo -->
        <div>
            <label for="kode" class="block text-sm font-medium mb-1">Kode Promo</label>
            <input type="text" name="kode" id="kode" placeholder="Masukkan kode promo" 
                   class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-primary focus:outline-none"
                   value="{{ old('kode') }}" required>
        </div>

        <!-- Diskon Persen -->
        <div>
            <label for="percent" class="block text-sm font-medium mb-1">Diskon Persen (%)</label>
            <input type="number" name="percent" id="percent" placeholder="Opsional" 
                   class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-primary focus:outline-none"
                   value="{{ old('percent') }}" min="0" max="100">
        </div>

        <!-- Diskon Nominal -->
        <div>
            <label for="amount" class="block text-sm font-medium mb-1">Diskon Nominal (Rp)</label>
            <input type="number" name="amount" id="amount" placeholder="Opsional" 
                   class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-primary focus:outline-none"
                   value="{{ old('amount') }}" min="0">
        </div>

        <!-- Tanggal Kadaluarsa -->
        <div>
            <label for="expired_at" class="block text-sm font-medium mb-1">Tanggal Kadaluarsa</label>
            <input type="date" name="expired_at" id="expired_at" 
                   class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-primary focus:outline-none"
                   value="{{ old('expired_at') }}">
        </div>

        <!-- Tombol Submit -->
        <div class="md:col-span-2 flex justify-end">
            <button type="submit" class="px-4 py-2 bg-primary text-white rounded hover:bg-primary/90">
                Simpan Promo
            </button>
        </div>
    </form>
</div>
<!-- Daftar Promo -->
<div class="bg-white rounded-lg p-4 md:p-6 shadow-sm mt-6">
    <h2 class="text-lg font-semibold mb-4">Daftar Promo</h2>

    @if(session('success'))
        <div class="p-2 mb-3 bg-green-100 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    <table class="w-full border-collapse">
        <thead>
            <tr class="bg-gray-100 text-left">
                <th class="p-2 border">Kode Promo</th>
                <th class="p-2 border">Diskon Persen (%)</th>
                <th class="p-2 border">Diskon Nominal (Rp)</th>
                <th class="p-2 border">Tanggal Kadaluarsa</th>
                <th class="p-2 border">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($promos as $promo)
            <tr>
                <td class="p-2 border font-semibold">{{ $promo->kode }}</td>
                <td class="p-2 border">
                    @if($promo->percent)
                        {{ $promo->percent }}%
                    @else
                        <span class="text-gray-400 text-sm">-</span>
                    @endif
                </td>
                <td class="p-2 border">
                    @if($promo->amount)
                        Rp{{ number_format($promo->amount, 0, ',', '.') }}
                    @else
                        <span class="text-gray-400 text-sm">-</span>
                    @endif
                </td>
                <td class="p-2 border">
                    @if($promo->expired_at)
                        {{ \Carbon\Carbon::parse($promo->expired_at)->format('d M Y') }}
                    @else
                        <span class="text-gray-400 text-sm">Tidak ditentukan</span>
                    @endif
                </td>
                <td class="p-2 border">
                    <form action="{{ route('admin.promo.destroy', $promo->id) }}" method="POST" 
                          class="inline-block"
                          onsubmit="return confirm('Yakin ingin menghapus promo ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-3 py-1 bg-red-600 text-white rounded">Hapus</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
        <!-- Charts Section -->
        <div class="bg-white rounded-lg p-4 md:p-6 shadow-sm w-full">
          <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js?v=1"></script>
          <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-2">
            <h2 class="text-lg font-semibold">Statistik Pemasukan</h2>
          </div>
          <div>
            <div id="chartPemasukanPerHari" class="chart-view">
              <canvas id="canvasHari" class="w-full"></canvas>
            </div>
          </div>
          <script>
            function showChart(chartId) {
              document.querySelectorAll('.chart-view').forEach(el => el.classList.add('hidden'));
              document.getElementById('chartPemasukanPerHari').classList.toggle('hidden', chartId !== 'hari');
            }
            // Chart Hari
            // Chart Hari
const chartHari = new Chart(document.getElementById('canvasHari'), {
    type: 'bar',  // Change type to bar
    data: {
        labels: [
            @foreach ($pemasukanHari as $key => $value)
                '{{ \Carbon\Carbon::createFromFormat('Y-m-d', $key)->isoFormat('D MMM Y') }}',
            @endforeach
        ],
        datasets: [{
            label: "Pemasukan",
            backgroundColor: 'rgba(75, 192, 192, 0.9)', // Adjust color as desired
            borderColor: 'rgb(75, 192, 192)',
            data: [
                @foreach ($pemasukanHari as $value)
                    {{ $value }},
                @endforeach
            ],
            borderWidth: 1 // Add border width for bars
        }]
    },
    options: {
        plugins: {
            legend: { display: true }
        },
        scales: {
            y: {
                beginAtZero: true, // Ensure y-axis starts at zero
                ticks: {
                    callback: function(value) {
                        return value.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
                    }
                }
            }
        }
    }
});

          </script>
          <!-- Form Filter Tanggal -->
          <form method="GET" action="{{ route('admin.dashboard') }}" class="mt-6 flex flex-col md:flex-row items-start md:items-center space-y-2 md:space-y-0 md:space-x-2">
            <label for="start_date" class="text-sm">Dari:</label>
            <input type="date" id="start_date" name="start_date" class="border rounded px-2 py-1 text-sm" value="{{ request('start_date') }}">
            <label for="end_date" class="text-sm">Sampai:</label>
            <input type="date" id="end_date" name="end_date" class="border rounded px-2 py-1 text-sm" value="{{ request('end_date') }}">
            <button type="submit" class="px-3 py-1 bg-primary text-white text-sm rounded">Tampilkan</button>
          </form>
          <!-- Chart Hasil Filter Tanggal -->
          <!-- Chart Hasil Filter Tanggal -->
@if (count($customData))
    <div class="mt-6">
        <h4 class="text-base font-semibold mb-2">Pemasukan dari {{ request('start_date') }} sampai {{ request('end_date') }}</h4>
        <canvas id="chartCustom"></canvas>
        <script>
            const chartCustom = new Chart(document.getElementById('chartCustom'), {
                type: 'bar', // Changed to bar type
                data: {
                    labels: [
                        @foreach ($customData as $tanggal => $value)
                            '{{ \Carbon\Carbon::parse($tanggal)->isoFormat('D MMM Y') }}',
                        @endforeach
                    ],
                    datasets: [{
                        label: 'Pemasukan',
                        data: [
                            @foreach ($customData as $value)
                                {{ $value }},
                            @endforeach
                        ],
                        backgroundColor: 'rgba(153, 102, 255, 0.5)', // Adjust the color for visibility
                        borderColor: 'rgba(153, 102, 255, 1)',
                        borderWidth: 1,
                        tension: 0 // Set tension to 0 for bar chart
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true // Starts the y-axis at zero
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Tanggal' // X-axis title
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'Jumlah Pemasukan' // Y-axis title
                            }
                        }
                    },
                    plugins: {
                        legend: { display: true }
                    }
                }
            });
        </script>
    </div>
@endif
