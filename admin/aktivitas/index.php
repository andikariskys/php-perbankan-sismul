<?php
session_start();
include "../../config/database.php";
include "../helpers/admin_auth.php";
include "../helpers/pagination.php";
include "../models/AktivitasModel.php";
include "../../helper/format.php";

adminGuard();

// Parameter filter/search
$search             = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_aktivitas   = isset($_GET['aktivitas']) ? trim($_GET['aktivitas']) : '';
$filter_tanggal_dari  = isset($_GET['dari']) ? trim($_GET['dari']) : '';
$filter_tanggal_sampai = isset($_GET['sampai']) ? trim($_GET['sampai']) : '';
$page               = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$per_page            = 15;

// Ambil data aktivitas
$aktivitas_data = getAktivitasList($conn, $search, $filter_aktivitas, $filter_tanggal_dari, $filter_tanggal_sampai, $page, $per_page);
$aktivitas_list = $aktivitas_data['data'];
$total_rows     = $aktivitas_data['total'];

// Daftar jenis aktivitas untuk filter
$jenis_aktivitas = getDistinctAktivitas($conn);

// Build base URL for pagination
$base_url = "index.php?search=" . urlencode($search)
    . "&aktivitas=" . urlencode($filter_aktivitas)
    . "&dari=" . urlencode($filter_tanggal_dari)
    . "&sampai=" . urlencode($filter_tanggal_sampai)
    . "&";
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aktivitas Sistem</title>

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
                        <a class="nav-link"
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
                        <a class="nav-link active"
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
                Aktivitas Sistem
            </h1>

            <p class="mb-0 opacity-75">
                Pantau aktivitas transaksi dan operasional bank.
            </p>

        </div>
    </header>

    <!-- Workspace -->
    <main class="flex-grow-1">
        <div class="container">

            <!-- Search & Filter -->
            <div class="card border-0 shadow-sm p-3 mb-4">
                <form method="GET" action="index.php" class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Pencarian</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" name="search" class="form-control" placeholder="Cari nama, email, aktivitas..." value="<?= htmlspecialchars($search); ?>">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Jenis Aktivitas</label>
                        <select name="aktivitas" class="form-select">
                            <option value="">Semua</option>
                            <?php foreach ($jenis_aktivitas as $ja): ?>
                                <option value="<?= htmlspecialchars($ja); ?>" <?= $filter_aktivitas === $ja ? 'selected' : ''; ?>><?= htmlspecialchars($ja); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Dari Tanggal</label>
                        <input type="date" name="dari" class="form-control" value="<?= htmlspecialchars($filter_tanggal_dari); ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Sampai Tanggal</label>
                        <input type="date" name="sampai" class="form-control" value="<?= htmlspecialchars($filter_tanggal_sampai); ?>">
                    </div>
                    <div class="col-md-1 d-grid">
                        <button type="submit" class="btn btn-primary fw-semibold">
                            <i class="fas fa-filter"></i>
                        </button>
                    </div>
                    <div class="col-md-2 d-grid">
                        <a href="index.php" class="btn btn-outline-secondary">Reset Filter</a>
                    </div>
                </form>
            </div>

            <!-- Tabel Aktivitas -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-semibold"><i class="fas fa-list-alt me-2 text-primary"></i>Aktivitas Nasabah <span class="badge bg-primary ms-1"><?= $total_rows; ?></span></h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal & Waktu</th>
                                    <th>Pengguna</th>
                                    <th>Role</th>
                                    <th>Aktivitas</th>
                                    <th>Deskripsi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($aktivitas_list)): ?>
                                    <?php
                                    $no = ($page - 1) * $per_page + 1;
                                    foreach ($aktivitas_list as $row):
                                        $badge_class = 'bg-secondary';
                                        $act = $row['aktivitas'];
                                        if ($act === 'Login' || $act === 'Logout') $badge_class = 'bg-info text-dark';
                                        elseif ($act === 'Ubah Password' || strpos($act, 'Password') !== false) $badge_class = 'bg-warning text-dark';
                                        elseif (strpos($act, 'Setor') !== false || $act === 'SETOR') $badge_class = 'bg-success';
                                        elseif (strpos($act, 'Tarik') !== false || $act === 'TARIK') $badge_class = 'bg-danger';
                                        elseif (strpos($act, 'Transfer') !== false || strpos($act, 'TRANSFER') !== false) $badge_class = 'bg-primary';
                                        elseif (strpos($act, 'Top Up') !== false || strpos($act, 'Topup') !== false || strpos($act, 'TOPUP') !== false) $badge_class = 'bg-dark';
                                        elseif (strpos($act, 'Ubah Profil') !== false) $badge_class = 'bg-info text-dark';
                                        elseif (strpos($act, 'Verifikasi') !== false) $badge_class = 'bg-success';
                                        elseif (strpos($act, 'Nonaktif') !== false) $badge_class = 'bg-danger';
                                        elseif (strpos($act, 'Aktivasi') !== false) $badge_class = 'bg-success';
                                        elseif (strpos($act, 'Reset') !== false) $badge_class = 'bg-warning text-dark';
                                    ?>
                                        <tr>
                                            <td><?= $no++; ?></td>
                                            <td><small class="text-muted"><?= date('d-m-Y H:i:s', strtotime($row['created_at'])); ?></small></td>
                                            <td>
                                                <div class="fw-semibold"><?= htmlspecialchars($row['nama_lengkap']); ?></div>
                                                <small class="text-muted"><code><?= htmlspecialchars($row['email']); ?></code></small>
                                            </td>
                                            <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($row['nama_role']); ?></span></td>
                                            <td><span class="badge <?= $badge_class; ?> px-2 py-1"><?= htmlspecialchars($act); ?></span></td>
                                            <td><small class="text-muted"><?= htmlspecialchars($row['deskripsi'] ?? '-'); ?></small></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">
                                            Tidak ada aktivitas ditemukan dengan filter aktif.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php if ($total_rows > $per_page): ?>
                    <div class="card-footer bg-white">
                        <?php renderPagination($total_rows, $per_page, $page, $base_url); ?>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-light border-top py-3">
        <div class="container-fluid text-center">
            <small>
                © <?= date('Y'); ?> Bank Multimedia
            </small>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>