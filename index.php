<?php
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bank Sismul - Layanan Perbankan Digital Terpercaya</title>
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            overflow-x: hidden;
            scroll-behavior: smooth;
        }
        .navbar {
            background-color: #ffffff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .navbar-brand {
            font-weight: 700;
            color: #0d6efd;
        }
        .hero-section {
            position: relative;
            min-height: 100vh;
            display: flex;
            align-items: center;
            color: white;
            overflow: hidden;
        }
        .hero-video {
            position: absolute;
            top: 50%;
            left: 50%;
            min-width: 100%;
            min-height: 100%;
            width: auto;
            height: auto;
            z-index: 0;
            transform: translateX(-50%) translateY(-50%);
            object-fit: cover;
        }
        .hero-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            z-index: 1;
        }
        .hero-section .container {
            position: relative;
            z-index: 2;
        }
        .hero-title {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
        }
        .feature-card {
            border: none;
            border-radius: 15px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
        }
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .feature-icon {
            font-size: 3rem;
            color: #0d6efd;
            margin-bottom: 1rem;
        }
        .cta-section {
            background-color: #f8f9fa;
        }
        .footer {
            background-color: #212529;
            color: white;
            padding: 50px 0;
        }
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-university me-2"></i>BANK SISMUL
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Fitur</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">Tentang Kami</a>
                    </li>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li class="nav-item ms-lg-3">
                            <?php 
                                $dashboard_path = (isset($_SESSION['nama_role']) && $_SESSION['nama_role'] == 'Admin') ? 'admin/dashboard' : 'nasabah/dashboard';
                            ?>
                            <a class="btn btn-primary" href="<?php echo $dashboard_path; ?>">Dashboard</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item ms-lg-3">
                            <a class="btn btn-outline-primary me-2" href="login.php">Masuk</a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-primary" href="register.php">Daftar</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <header class="hero-section">
        <video autoplay muted loop class="hero-video">
            <source src="assets/videos/video.mp4" type="video/mp4">
            Your browser does not support the video tag.
        </video>
        <div class="hero-overlay"></div>
        <div class="container text-center text-lg-start">
            <div class="row align-items-center">
                <div class="col-lg-7">
                    <h1 class="hero-title">Perbankan Masa Kini: Aman, Cepat, & Mudah</h1>
                    <p class="lead mb-4">Nikmati kemudahan transaksi perbankan dalam genggaman Anda. Mulai dari transfer, tarik tunai, hingga top up e-wallet dengan keamanan ekstra.</p>
                    <div class="d-flex justify-content-center justify-content-lg-start gap-3">
                        <a href="register.php" class="btn btn-primary shadow rounded-pill btn-lg">Buka Rekening Sekarang</a>
                        <a href="#features" class="btn btn-outline-light btn-lg">Pelajari Fitur</a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Features Section -->
    <section id="features" class="py-5">
        <div class="container py-5">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Layanan Unggulan Kami</h2>
                <p class="text-muted">Segala kebutuhan transaksi perbankan Anda terpenuhi dalam satu aplikasi.</p>
            </div>
            <div class="row g-4">
                <!-- Feature 1 -->
                <div class="col-md-6 col-lg-3 text-center">
                    <div class="card h-100 p-4 feature-card shadow-sm">
                        <div class="card-body">
                            <i class="fas fa-exchange-alt feature-icon"></i>
                            <h5 class="fw-bold">Transfer</h5>
                            <p class="text-muted">Kirim dana antar rekening dengan instan dan aman kapanpun.</p>
                        </div>
                    </div>
                </div>
                <!-- Feature 2 -->
                <div class="col-md-6 col-lg-3 text-center">
                    <div class="card h-100 p-4 feature-card shadow-sm">
                        <div class="card-body">
                            <i class="fas fa-money-bill-wave feature-icon"></i>
                            <h5 class="fw-bold">Tarik Tunai</h5>
                            <p class="text-muted">Simulasi penarikan dana dengan mudah tanpa ribet.</p>
                        </div>
                    </div>
                </div>
                <!-- Feature 3 -->
                <div class="col-md-6 col-lg-3 text-center">
                    <div class="card h-100 p-4 feature-card shadow-sm">
                        <div class="card-body">
                            <i class="fas fa-wallet feature-icon"></i>
                            <h5 class="fw-bold">Top Up</h5>
                            <p class="text-muted">Isi saldo OVO, DANA, GoPay, dan ShopeePay dengan biaya rendah.</p>
                        </div>
                    </div>
                </div>
                <!-- Feature 4 -->
                <div class="col-md-6 col-lg-3 text-center">
                    <div class="card h-100 p-4 feature-card shadow-sm">
                        <div class="card-body">
                            <i class="fas fa-lock feature-icon"></i>
                            <h5 class="fw-bold">Keamanan</h5>
                            <p class="text-muted">Data sensitif dilindungi dengan standar enkripsi AES-256.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="cta-section py-5">
        <div class="container py-5">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <img src="https://images.unsplash.com/photo-1563986768609-322da13575f3?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Mobile Banking" class="img-fluid rounded-4 shadow-lg">
                </div>
                <div class="col-lg-6 ps-lg-5">
                    <h2 class="fw-bold mb-4">Teknologi Multimedia & Keamanan Data</h2>
                    <p class="text-muted mb-4">Bank Sismul bukan sekadar aplikasi perbankan biasa. Dikembangkan sebagai proyek Sistem Multimedia, kami mengutamakan pengalaman visual dan keamanan tingkat tinggi.</p>
                    <ul class="list-unstyled mb-4">
                        <li class="mb-2"><i class="fas fa-check-circle text-primary me-2"></i> Kompresi gambar profil otomatis</li>
                        <li class="mb-2"><i class="fas fa-check-circle text-primary me-2"></i> Enkripsi nomor rekening & data sensitif</li>
                        <li class="mb-2"><i class="fas fa-check-circle text-primary me-2"></i> Sistem resi transaksi otomatis</li>
                        <li class="mb-2"><i class="fas fa-check-circle text-primary me-2"></i> Audit log aktivitas pengguna</li>
                    </ul>
                    <a href="register.php" class="btn btn-primary shadow">Bergabung Bersama Kami</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4 mb-lg-0">
                    <h4 class="fw-bold mb-4"><i class="fas fa-university me-2"></i>BANK SISMUL</h4>
                    <p class="text-secondary">Solusi perbankan digital modern untuk kebutuhan multimedia dan keamanan data Anda.</p>
                    <div class="d-flex gap-3">
                        <a href="#" class="text-white fs-4"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="text-white fs-4"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-white fs-4"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 mb-4 mb-lg-0">
                    <h5 class="fw-bold mb-4">Layanan</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#" class="text-secondary text-decoration-none">Transfer</a></li>
                        <li class="mb-2"><a href="#" class="text-secondary text-decoration-none">Setor Dana</a></li>
                        <li class="mb-2"><a href="#" class="text-secondary text-decoration-none">Tarik Dana</a></li>
                        <li class="mb-2"><a href="#" class="text-secondary text-decoration-none">Top Up</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-4 mb-4 mb-lg-0">
                    <h5 class="fw-bold mb-4">Bantuan</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#" class="text-secondary text-decoration-none">FAQ</a></li>
                        <li class="mb-2"><a href="#" class="text-secondary text-decoration-none">Hubungi Kami</a></li>
                        <li class="mb-2"><a href="#" class="text-secondary text-decoration-none">Kebijakan Privasi</a></li>
                    </ul>
                </div>
                <div class="col-lg-4 col-md-4">
                    <h5 class="fw-bold mb-4">Lokasi Kami</h5>
                    <p class="text-secondary"><i class="fas fa-map-marker-alt me-2"></i> Kampus Universitas Muhammadiyah Surakarta</p>
                    <p class="text-secondary"><i class="fas fa-envelope me-2"></i> support@banksismul.com</p>
                </div>
            </div>
            <hr class="my-4 bg-secondary">
            <div class="text-center text-secondary">
                <p>&copy; <?= date('Y'); ?> Bank Sismul. All rights reserved. Kelompok 3 - Sistem Multimedia.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>