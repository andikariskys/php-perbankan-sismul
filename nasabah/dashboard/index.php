<?php
session_start();
include "../../config/database.php";
include "../../helper/format.php";
include "../../helper/encryption.php";

if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php?pesan=belum_login');
    exit;
}

if ($_SESSION['nama_role'] !== 'Nasabah') {
    header('Location: /login.php?pesan=akses_ditolak');
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil data user
$user_query = mysqli_query($conn, "SELECT nama_lengkap FROM users WHERE id = '$user_id'");
$user_data = mysqli_fetch_assoc($user_query);
$nama_user = $user_data['nama_lengkap'] ?? 'Nasabah';

// Ambil ringkasan rekening
$rekening_query = mysqli_query($conn, "SELECT * FROM rekening WHERE user_id = '$user_id'");
$total_saldo = 0;
$jumlah_rekening = mysqli_num_rows($rekening_query);
$rekening_list = [];

while ($row = mysqli_fetch_assoc($rekening_query)) {
    $total_saldo += $row['saldo'];
    $rekening_list[] = $row;
}

// Ambil transaksi terbaru (gabungan dari semua rekening milik nasabah)
$transaksi_query = mysqli_query($conn, "
    SELECT t.*, r.nomor_rekening_encrypted, r.jenis_rekening 
    FROM transaksi t
    JOIN rekening r ON t.rekening_id = r.id
    WHERE r.user_id = '$user_id'
    ORDER BY t.created_at DESC
    LIMIT 5
");
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Nasabah</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .hero {
            background: linear-gradient(135deg, #198754, #146c43);
            color: white;
        }

        .card-stats {
            transition: transform 0.2s;
        }

        .card-stats:hover {
            transform: translateY(-5px);
        }

        .quick-action-btn {
            transition: all 0.2s;
            border-radius: 12px;
        }

        .quick-action-btn:hover {
            background-color: #f8f9fa;
            transform: scale(1.05);
        }
    </style>
</head>

<body class="d-flex flex-column min-vh-100 bg-light">

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-success">
        <div class="container-fluid">

            <a class="navbar-brand fw-bold" href="#">
                Bank Multimedia
            </a>

            <button class="navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navbarNasabah">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNasabah">

                <ul class="navbar-nav me-auto">

                    <li class="nav-item">
                        <a class="nav-link active"
                            href="../dashboard/index.php">
                            Dashboard
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link"
                            href="../profil/index.php">
                            Profil
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link"
                            href="../rekening/index.php">
                            Rekening
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link"
                            href="../riwayat/index.php">
                            Riwayat
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link"
                            href="../setor/index.php">
                            Setor
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link"
                            href="../tarik/index.php">
                            Tarik
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link"
                            href="../transfer/index.php">
                            Transfer
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link"
                            href="../topup/index.php">
                            Top Up
                        </a>
                    </li>

                </ul>

                <div class="d-flex align-items-center">
                    <span class="text-white me-3 d-none d-lg-inline">
                        <i class="fas fa-user-circle me-1"></i> <?= htmlspecialchars($nama_user); ?>
                    </span>
                    <a href="../../logout.php"
                        class="btn btn-light btn-sm">
                        Logout
                    </a>
                </div>

            </div>
        </div>
    </nav>

    <!-- Header -->
    <header class="hero py-5 shadow-sm">
        <div class="container">
            <h1 class="display-6 fw-bold">
                Selamat Datang, <?= explode(' ', $nama_user)[0]; ?>!
            </h1>
            <p class="mb-0 opacity-75">
                Pantau saldo dan aktivitas keuangan Anda dengan mudah dan aman.
            </p>
        </div>
    </header>

    <main class="flex-grow-1">
        <div class="container py-4">

            <!-- Statistik Singkat -->
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm card-stats h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                                <i class="fas fa-wallet fa-2x text-success"></i>
                            </div>
                            <div>
                                <div class="text-muted small">Total Saldo (Semua Rekening)</div>
                                <div class="fw-bold fs-3 text-success"><?= formatCurrency($total_saldo); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm card-stats h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                                <i class="fas fa-credit-card fa-2x text-primary"></i>
                            </div>
                            <div>
                                <div class="text-muted small">Jumlah Rekening Aktif</div>
                                <div class="fw-bold fs-3 text-primary"><?= number_format($jumlah_rekening); ?> <span class="fs-6 fw-normal text-muted">Rekening</span></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <!-- Kolom Kiri: Akses Cepat & Rekening -->
                <div class="col-lg-4">
                    <h5 class="mb-3 fw-bold"><i class="fas fa-bolt me-2 text-warning"></i>Akses Cepat</h5>
                    <div class="row g-2 mb-4">
                        <div class="col-6">
                            <a href="../transfer/index.php" class="card border-0 shadow-sm text-decoration-none text-dark quick-action-btn">
                                <div class="card-body text-center py-3">
                                    <i class="fas fa-exchange-alt fa-2x text-primary mb-2"></i>
                                    <div class="small fw-bold">Transfer</div>
                                </div>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="../topup/index.php" class="card border-0 shadow-sm text-decoration-none text-dark quick-action-btn">
                                <div class="card-body text-center py-3">
                                    <i class="fas fa-mobile-alt fa-2x text-info mb-2"></i>
                                    <div class="small fw-bold">Top Up</div>
                                </div>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="../setor/index.php" class="card border-0 shadow-sm text-decoration-none text-dark quick-action-btn">
                                <div class="card-body text-center py-3">
                                    <i class="fas fa-arrow-down fa-2x text-success mb-2"></i>
                                    <div class="small fw-bold">Setor</div>
                                </div>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="../tarik/index.php" class="card border-0 shadow-sm text-decoration-none text-dark quick-action-btn">
                                <div class="card-body text-center py-3">
                                    <i class="fas fa-arrow-up fa-2x text-danger mb-2"></i>
                                    <div class="small fw-bold">Tarik</div>
                                </div>
                            </a>
                        </div>
                    </div>

                    <h5 class="mb-3 fw-bold"><i class="fas fa-list me-2 text-primary"></i>Rekening Anda</h5>
                    <?php if ($jumlah_rekening > 0): ?>
                        <?php foreach ($rekening_list as $rek): ?>
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="badge bg-success-subtle text-success small"><?= htmlspecialchars($rek['jenis_rekening']); ?></span>
                                        <small class="text-muted"><?= htmlspecialchars($rek['status_rekening']); ?></small>
                                    </div>
                                    <div class="fw-bold font-monospace"><?= htmlspecialchars(decrypt($rek['nomor_rekening_encrypted'])); ?></div>
                                    <div class="text-success fw-bold"><?= formatCurrency($rek['saldo']); ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <div class="text-center">
                            <a href="../rekening/index.php" class="btn btn-sm btn-link text-success text-decoration-none fw-bold">Lihat Semua Rekening</a>
                        </div>
                    <?php else: ?>
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-body text-center py-4">
                                <p class="text-muted small mb-3">Anda belum memiliki rekening aktif.</p>
                                <a href="../rekening/index.php" class="btn btn-sm btn-success">Buka Rekening</a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Kolom Kanan: Transaksi Terakhir -->
                <div class="col-lg-8">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0 fw-bold"><i class="fas fa-history me-2 text-info"></i>Transaksi Terakhir</h5>
                        <a href="../riwayat/index.php" class="btn btn-sm btn-outline-success">Lihat Semua</a>
                    </div>
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Waktu</th>
                                            <th>Transaksi</th>
                                            <th class="text-end">Nominal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (mysqli_num_rows($transaksi_query) > 0): ?>
                                            <?php while ($trx = mysqli_fetch_assoc($transaksi_query)): ?>
                                                <?php
                                                $nominal_class = 'text-dark';
                                                $icon = 'fa-circle-dot';
                                                $jenis = $trx['jenis_transaksi'];

                                                if (in_array($jenis, ['SETOR', 'TRANSFER_MASUK'])) {
                                                    $nominal_class = 'text-success';
                                                    $icon = 'fa-arrow-down';
                                                    $prefix = '+ ';
                                                } else {
                                                    $nominal_class = 'text-danger';
                                                    $icon = 'fa-arrow-up';
                                                    $prefix = '- ';
                                                }
                                                ?>
                                                <tr>
                                                    <td>
                                                        <small class="text-muted d-block"><?= date('d M Y', strtotime($trx['created_at'])); ?></small>
                                                        <small class="text-muted" style="font-size: 0.75rem;"><?= date('H:i', strtotime($trx['created_at'])); ?></small>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="rounded-circle bg-light p-2 me-3 text-center" style="width: 35px; height: 35px;">
                                                                <i class="fas <?= $icon; ?> small <?= $nominal_class; ?>"></i>
                                                            </div>
                                                            <div>
                                                                <div class="fw-bold small"><?= str_replace('_', ' ', $jenis); ?></div>
                                                                <small class="text-muted d-block" style="font-size: 0.75rem;">
                                                                    Rek: <?= substr(decrypt($trx['nomor_rekening_encrypted']), 0, 8); ?>...
                                                                </small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="text-end fw-bold <?= $nominal_class; ?>">
                                                        <?= $prefix . formatCurrency($trx['nominal']); ?>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="3" class="text-center py-5">
                                                    <img src="../../assets/images/broken-machine.jpg" alt="No data" class="mb-3 opacity-50" style="width: 100px;">
                                                    <p class="text-muted">Belum ada transaksi tercatat.</p>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-light border-top py-3 mt-auto">
        <div class="container-fluid text-center">
            <small class="text-muted">
                © <?= date('Y'); ?> Bank Multimedia - Solusi Perbankan Digital Masa Depan
            </small>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>