<?php
session_start();
include "../../config/database.php";
include "../helpers/admin_auth.php";
include "../helpers/pagination.php";
include "../models/RekeningModel.php";
include "../../helper/format.php";
include "../../helper/encryption.php";

adminGuard();

// Catat audit log: admin melihat data rekening
catatAuditAdmin($conn, $_SESSION['user_id'], $_SESSION['user_id'], 'Lihat Data Rekening', 'Admin mengakses halaman kelola rekening');

// Parameter filter/search
$search        = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_status = isset($_GET['status']) ? trim($_GET['status']) : '';
$page          = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$per_page      = 10;

// Detail rekening
$detail = null;
if (isset($_GET['detail'])) {
    $detail_id = (int) $_GET['detail'];
    $detail = getRekeningById($conn, $detail_id);
}

// Ambil data rekening
$rekening_data = getRekeningList($conn, $search, $filter_status, $page, $per_page);
$rekening_list = $rekening_data['data'];
$total_rows    = $rekening_data['total'];

// Build base URL for pagination
$base_url = "index.php?search=" . urlencode($search) . "&status=" . urlencode($filter_status) . "&";
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Rekening</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .hero {
            background: linear-gradient(135deg, #fd7e14, #b45309);
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
                        <a class="nav-link active"
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
                Kelola Rekening
            </h1>

            <p class="mb-0 opacity-75">
                Pantau dan kelola seluruh rekening nasabah.
            </p>

        </div>
    </header>

    <!-- Workspace -->
    <main class="flex-grow-1">
        <div class="container-fluid py-4">

            <?php if ($detail): ?>
                <!-- Detail Rekening -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-semibold"><i class="fas fa-credit-card me-2 text-warning"></i>Detail Rekening</h6>
                        <a href="index.php?search=<?= urlencode($search); ?>&status=<?= urlencode($filter_status); ?>&page=<?= $page; ?>" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Kembali
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless table-sm">
                                    <tr><td class="text-muted fw-semibold">ID Rekening</td><td><?= $detail['id']; ?></td></tr>
                                    <tr><td class="text-muted fw-semibold">Nomor Rekening</td><td><code><?= htmlspecialchars(decrypt($detail['nomor_rekening_encrypted'])); ?></code></td></tr>
                                    <tr><td class="text-muted fw-semibold">Jenis Rekening</td><td><?= htmlspecialchars($detail['jenis_rekening']); ?></td></tr>
                                    <tr><td class="text-muted fw-semibold">Saldo</td><td class="fw-bold text-success fs-5"><?= formatCurrency($detail['saldo']); ?></td></tr>
                                    <tr>
                                        <td class="text-muted fw-semibold">Status Rekening</td>
                                        <td><span class="badge <?= $detail['status_rekening'] === 'Aktif' ? 'bg-success' : 'bg-danger'; ?>"><?= htmlspecialchars($detail['status_rekening']); ?></span></td>
                                    </tr>
                                    <tr><td class="text-muted fw-semibold">Tanggal Pembuatan</td><td><?= date('d M Y H:i', strtotime($detail['created_at'])); ?></td></tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6 class="fw-semibold mb-3"><i class="fas fa-user me-1 text-primary"></i>Pemilik Rekening</h6>
                                <table class="table table-borderless table-sm">
                                    <tr><td class="text-muted fw-semibold">Nama Lengkap</td><td><?= htmlspecialchars($detail['nama_lengkap']); ?></td></tr>
                                    <tr><td class="text-muted fw-semibold">Email</td><td><code><?= htmlspecialchars($detail['email']); ?></code></td></tr>
                                    <tr><td class="text-muted fw-semibold">No. HP</td><td><?= htmlspecialchars($detail['no_hp'] ?? '-'); ?></td></tr>
                                </table>
                                <a href="../nasabah/index.php?detail=<?= $detail['user_id']; ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-external-link-alt me-1"></i>Lihat Profil Nasabah
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Search & Filter -->
            <div class="card border-0 shadow-sm p-3 mb-4">
                <form method="GET" action="index.php" class="row g-2 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label small fw-semibold">Pencarian</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" name="search" class="form-control" placeholder="Cari nama pemilik atau email..." value="<?= htmlspecialchars($search); ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Filter Status</label>
                        <select name="status" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="Aktif" <?= $filter_status === 'Aktif' ? 'selected' : ''; ?>>Aktif</option>
                            <option value="Blokir" <?= $filter_status === 'Blokir' ? 'selected' : ''; ?>>Blokir</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-grid">
                        <button type="submit" class="btn btn-primary fw-semibold">
                            <i class="fas fa-filter me-1"></i>Terapkan
                        </button>
                    </div>
                    <div class="col-md-2 d-grid">
                        <a href="index.php" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </form>
            </div>

            <!-- Tabel Rekening -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-semibold"><i class="fas fa-credit-card me-2 text-warning"></i>Daftar Rekening <span class="badge bg-warning text-dark ms-1"><?= $total_rows; ?></span></h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Nomor Rekening</th>
                                    <th>Nama Pemilik</th>
                                    <th>Jenis</th>
                                    <th>Saldo</th>
                                    <th>Status</th>
                                    <th>Tgl Pembuatan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($rekening_list)): ?>
                                    <?php
                                    $no = ($page - 1) * $per_page + 1;
                                    foreach ($rekening_list as $r):
                                    ?>
                                        <tr>
                                            <td><?= $no++; ?></td>
                                            <td><code><?= htmlspecialchars(decrypt($r['nomor_rekening_encrypted'])); ?></code></td>
                                            <td>
                                                <div class="fw-semibold"><?= htmlspecialchars($r['nama_lengkap']); ?></div>
                                                <small class="text-muted"><?= htmlspecialchars($r['email']); ?></small>
                                            </td>
                                            <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($r['jenis_rekening']); ?></span></td>
                                            <td class="fw-semibold"><?= formatCurrency($r['saldo']); ?></td>
                                            <td>
                                                <span class="badge <?= $r['status_rekening'] === 'Aktif' ? 'bg-success' : 'bg-danger'; ?>">
                                                    <?= htmlspecialchars($r['status_rekening']); ?>
                                                </span>
                                            </td>
                                            <td><small class="text-muted"><?= date('d-m-Y', strtotime($r['created_at'])); ?></small></td>
                                            <td>
                                                <a href="index.php?detail=<?= $r['id']; ?>&search=<?= urlencode($search); ?>&status=<?= urlencode($filter_status); ?>&page=<?= $page; ?>" class="btn btn-outline-primary btn-sm" title="Detail">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">Tidak ada data rekening ditemukan.</td>
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