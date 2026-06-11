<?php
session_start();
include '../../config/database.php';
include '../helpers/admin_auth.php';
include '../models/DashboardModel.php';
include '../../helper/format.php';

adminGuard();

// Catat audit log: admin melihat dashboard
catatAuditAdmin($conn, $_SESSION['user_id'], $_SESSION['user_id'], 'Lihat Dashboard', 'Admin mengakses halaman dashboard');

// Ambil data realtime dari database
$total_nasabah    = getTotalNasabah($conn);
$total_rekening   = getTotalRekening($conn);
$total_transaksi  = getTotalTransaksi($conn);
$total_saldo      = getTotalSaldo($conn);
$akun_aktif       = getJumlahAkunAktif($conn);
$akun_pending     = getJumlahAkunPending($conn);
$aktivitas_recent = getAktivitasTerbaru($conn, 10);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .hero {
            background: linear-gradient(135deg, #0d6efd, #084298);
            color: white;
        }
    </style>
</head>

<body class="d-flex flex-column min-vh-100">

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">

            <a class="navbar-brand fw-bold" href="#">
                Bank Multimedia
            </a>

            <button class="navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navbarAdmin">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarAdmin">

                <ul class="navbar-nav me-auto">

                    <li class="nav-item">
                        <a class="nav-link active"
                            href="../dashboard/index.php">
                            Dashboard
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link"
                            href="../nasabah/index.php">
                            Nasabah
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
                            href="../aktivitas/index.php">
                            Aktivitas
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link"
                            href="../logs/index.php">
                            Logs
                        </a>
                    </li>

                </ul>

                <a href="../../logout.php"
                    class="btn btn-light btn-sm">
                    Logout
                </a>

            </div>
        </div>
    </nav>

    <!-- HEADER -->
    <header class="hero py-5 shadow-sm">
        <div class="container">

            <h1 class="display-6 fw-bold">
                Dashboard Admin
            </h1>

            <p class="mb-0 opacity-75">
                Kelola data nasabah, rekening, aktivitas sistem, dan audit log.
            </p>

        </div>
    </header>

    <!-- Workspace -->
    <main class="flex-grow-1">
        <div class="container py-4">

            <!-- Statistik Cards Row 1 -->
            <div class="row g-3 mb-4">
                <div class="col-md-6 col-lg-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                                <i class="fas fa-users fa-lg text-primary"></i>
                            </div>
                            <div>
                                <div class="text-muted small">Total Nasabah</div>
                                <div class="fw-bold fs-4"><?= number_format($total_nasabah); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                                <i class="fas fa-credit-card fa-lg text-success"></i>
                            </div>
                            <div>
                                <div class="text-muted small">Total Rekening</div>
                                <div class="fw-bold fs-4"><?= number_format($total_rekening); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="rounded-circle bg-info bg-opacity-10 p-3 me-3">
                                <i class="fas fa-exchange-alt fa-lg text-info"></i>
                            </div>
                            <div>
                                <div class="text-muted small">Total Transaksi</div>
                                <div class="fw-bold fs-4"><?= number_format($total_transaksi); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="rounded-circle bg-warning bg-opacity-10 p-3 me-3">
                                <i class="fas fa-wallet fa-lg text-warning"></i>
                            </div>
                            <div>
                                <div class="text-muted small">Total Saldo</div>
                                <div class="fw-bold fs-5"><?= formatCurrency($total_saldo); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistik Cards Row 2 -->
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                                <i class="fas fa-check-circle fa-lg text-success"></i>
                            </div>
                            <div>
                                <div class="text-muted small">Akun Aktif</div>
                                <div class="fw-bold fs-4"><?= number_format($akun_aktif); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="rounded-circle bg-danger bg-opacity-10 p-3 me-3">
                                <i class="fas fa-clock fa-lg text-danger"></i>
                            </div>
                            <div>
                                <div class="text-muted small">Pending Verifikasi</div>
                                <div class="fw-bold fs-4"><?= number_format($akun_pending); ?></div>
                            </div>
                            <?php if ($akun_pending > 0): ?>
                                <a href="../nasabah/index.php?filter=Pending" class="btn btn-outline-danger btn-sm ms-auto">Verifikasi</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Aktivitas Terbaru -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-semibold"><i class="fas fa-clock-rotate-left me-2 text-primary"></i>Aktivitas Terbaru Nasabah</h6>
                    <a href="../logs/index.php" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Waktu</th>
                                    <th>Pengguna</th>
                                    <th>Aktivitas</th>
                                    <th>Deskripsi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($aktivitas_recent)): ?>
                                    <?php foreach ($aktivitas_recent as $act): ?>
                                        <?php
                                        $badge_class = 'bg-secondary';
                                        $a = $act['aktivitas'];
                                        if ($a === 'Login' || $a === 'Logout') $badge_class = 'bg-info text-dark';
                                        elseif (strpos($a, 'Password') !== false) $badge_class = 'bg-warning text-dark';
                                        elseif (strpos($a, 'Setor') !== false || $a === 'SETOR') $badge_class = 'bg-success';
                                        elseif (strpos($a, 'Tarik') !== false || $a === 'TARIK') $badge_class = 'bg-danger';
                                        elseif (strpos($a, 'Transfer') !== false) $badge_class = 'bg-primary';
                                        elseif (strpos($a, 'Top Up') !== false || strpos($a, 'Topup') !== false || strpos($a, 'TOPUP') !== false) $badge_class = 'bg-dark';
                                        elseif (strpos($a, 'Verifikasi') !== false) $badge_class = 'bg-success';
                                        elseif (strpos($a, 'Aktivasi') !== false) $badge_class = 'bg-success';
                                        elseif (strpos($a, 'Nonaktif') !== false) $badge_class = 'bg-danger';
                                        elseif (strpos($a, 'Reset') !== false) $badge_class = 'bg-warning text-dark';
                                        ?>
                                        <tr>
                                            <td><small class="text-muted"><?= date('d-m-y H:i', strtotime($act['created_at'])); ?></small></td>
                                            <td>
                                                <div class="fw-semibold"><?= htmlspecialchars($act['nama_lengkap']); ?></div>
                                                <small class="text-muted"><?= htmlspecialchars($act['email']); ?></small>
                                            </td>
                                            <td><span class="badge <?= $badge_class; ?> px-2 py-1"><?= htmlspecialchars($a); ?></span></td>
                                            <td><small class="text-muted"><?= htmlspecialchars($act['deskripsi'] ?? '-'); ?></small></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">Belum ada aktivitas tercatat.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-light border-top py-3">
        <div class="container text-center">
            <small>
                © <?= date('Y'); ?> Bank Multimedia
            </small>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>