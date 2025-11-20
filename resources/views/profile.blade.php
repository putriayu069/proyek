<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profil Saya</title>
    <meta name="viewport" content="width=device-width, initial-scale=1"> <!-- buat responsive -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/profile.css') }}">
    <style>
        .header-bar {
            background-color: #C4B8A8;
            color: white;
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .contact-box {
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            padding: 24px;
            max-width: 400px;
            margin: 0 auto;
            text-align: center;
        }

        .contact-button {
            display: block;
            background-color: #C4B8A8;
            color: #333;
            text-decoration: none;
            font-weight: bold;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 12px;
            transition: background-color 0.3s ease;
        }

        .contact-button:hover {
            background-color: #b3a38e;
        }

        .profile-form input {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 8px;
            border: 1px solid #ccc;
        }

        .save-btn {
            background-color: #C4B8A8;
            color: #fff;
            border: none;
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            font-weight: bold;
        }

        .save-btn:hover {
            background-color: #b3a38e;
        }

        /* Responsive tweaks */
        @media (max-width: 576px) {
            .header-bar {
                font-size: 20px;
                padding: 10px;
            }

            .contact-box {
                padding: 16px;
                max-width: 100%;
                margin: 0 10px;
            }
        }
    </style>
</head>
<body>
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

    <div class="profile px-4 py-5">
        <div class="header-bar">Update Profil</div>

        {{-- Alert sukses --}}
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        {{-- Alert error --}}
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="profile-form">
            <div class="image-upload text-center mb-3">
                @if(Auth::user()->profile_image)
                    <img id="preview" src="{{ asset('storage/profile_images/' . Auth::user()->profile_image) }}" alt="Profil" class="rounded-circle" style="width: 100px; height: 100px; object-fit: cover;">
                @else
                    <img id="preview" src="{{ asset('images/icon profil.png') }}" alt="Profil" class="rounded-circle" style="width: 100px; height: 100px; object-fit: cover;">
                @endif
            </div>

            <form method="POST" action="{{ route('profile.update') }}">
                @csrf
                @method('PUT')

                <input type="text" name="name" placeholder="Nama Lengkap" required value="{{ old('name', Auth::user()->name) }}">
                <input type="email" name="email" placeholder="Email" required value="{{ old('email', Auth::user()->email) }}">
                <input type="text" name="phone" placeholder="No. Handphone" value="{{ old('phone', Auth::user()->phone) }}">
                <select name="gender" class="form-select" required>
                    <option value="Laki-laki" {{ old('gender', Auth::user()->gender) == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                    <option value="Perempuan" {{ old('gender', Auth::user()->gender) == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                </select>

                <input type="text" name="address" placeholder="Alamat" value="{{ old('address', Auth::user()->address) }}">

                <button type="submit" class="save-btn btn mt-2">Simpan</button>
            </form>
        </div>
    </div>

    <div class="header-bar mt-5">Kontak Bantuan</div>
    <div class="contact-box">
        <h2>Kontak Bantuan</h2>
        <p>Klik nomor di bawah untuk langsung chat via WhatsApp:</p>

        <a 
            href="https://wa.me/6281311394644?text=Halo%20admin,%20saya%20butuh%20bantuan"
            target="_blank"
            class="contact-button"
        >
            +62 813-1139-4644 (WhatsApp)
        </a>

        <a 
            href="https://mail.google.com/mail/?view=cm&fs=1&to=emebel.properti@gmail.com&su=Butuh%20Bantuan&body=Halo%20admin,%20saya%20memerlukan%20bantuan."
            target="_blank"
            class="contact-button"
        >
            emebel.properti@gmail.com (Email)
        </a>
    </div>

    {{-- Footer --}}
        <footer class="bg-dark text-white text-center py-3 mt-4">
            &copy; {{ date('Y') }} E-Mebel. All Rights Reserved.
        </footer>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
