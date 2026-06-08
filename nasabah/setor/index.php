<?php
session_start();
include "../../config/database.php";
include "../../helper/encryption.php";
include "../../helper/format.php";

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php?pesan=belum_login');
    exit;
}

if ($_SESSION['nama_role'] !== 'Nasabah') {
    header('Location: ../../login.php?pesan=akses_ditolak');
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil rekening milik nasabah yang aktif
$query_rekening = "SELECT id, nomor_rekening_encrypted, jenis_rekening, saldo 
                   FROM rekening 
                   WHERE user_id = ? AND status_rekening = 'Aktif'";
$stmt_rekening = mysqli_prepare($conn, $query_rekening);
mysqli_stmt_bind_param($stmt_rekening, "i", $user_id);
mysqli_stmt_execute($stmt_rekening);
$result_rekening = mysqli_stmt_get_result($stmt_rekening);

// Ambil daftar merchant aktif
$query_merchant = "SELECT id, nama_merchant FROM merchant WHERE status_merchant = 'Aktif'";
$result_merchant = mysqli_query($conn, $query_merchant);

// Pagination riwayat setor
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Hitung total data riwayat setor
$query_count = "SELECT COUNT(*) as total 
                FROM transaksi t
                JOIN rekening r ON t.rekening_id = r.id
                WHERE r.user_id = ? AND t.jenis_transaksi = 'SETOR'";
$stmt_count = mysqli_prepare($conn, $query_count);
mysqli_stmt_bind_param($stmt_count, "i", $user_id);
mysqli_stmt_execute($stmt_count);
$result_count = mysqli_stmt_get_result($stmt_count);
$total_data = mysqli_fetch_assoc($result_count)['total'];
$total_pages = ceil($total_data / $per_page);

// Ambil riwayat setor dengan pagination
$query_riwayat = "SELECT t.id, t.nominal, t.saldo_sebelum, t.saldo_sesudah, t.keterangan, t.created_at,
                         r.nomor_rekening_encrypted
                  FROM transaksi t
                  JOIN rekening r ON t.rekening_id = r.id
                  WHERE r.user_id = ? AND t.jenis_transaksi = 'SETOR'
                  ORDER BY t.created_at DESC
                  LIMIT ? OFFSET ?";
$stmt_riwayat = mysqli_prepare($conn, $query_riwayat);
mysqli_stmt_bind_param($stmt_riwayat, "iii", $user_id, $per_page, $offset);
mysqli_stmt_execute($stmt_riwayat);
$result_riwayat = mysqli_stmt_get_result($stmt_riwayat);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setor Dana - Bank Sismul</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .hero {
            background: linear-gradient(135deg, #ffc107, #b8860b);
            color: white;
        }
    </style>
</head>

<body class="d-flex flex-column min-vh-100">

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
                        <a class="nav-link"
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
                        <a class="nav-link active"
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

                <a href="../../logout.php"
                    class="btn btn-light btn-sm">
                    Logout
                </a>

            </div>
        </div>
    </nav>

    <!-- Header -->
    <header class="hero py-5 shadow-sm">
        <div class="container">
            <h1 class="display-6 fw-bold">
                Setor Dana
            </h1>
            <p class="mb-0 opacity-75">
                Ajukan setor dana ke rekening Anda melalui merchant yang tersedia.
            </p>
        </div>
    </header>

    <main class="flex-grow-1">
        <div class="container py-4">

            <!-- Alert Notifikasi -->
            <?php if (isset($_GET['pesan'])): ?>
                <?php $pesan = $_GET['pesan']; ?>

                <?php if ($pesan == 'berhasil'): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <strong>Berhasil!</strong> Setor dana berhasil dilakukan.
                        <?php if (isset($_GET['transaksi_id'])): ?>
                            <a href="cetak_resi.php?id=<?= (int)$_GET['transaksi_id'] ?>" class="alert-link ms-2">
                                <i class="fas fa-print me-1"></i>Cetak Resi
                            </a>
                        <?php endif; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>

                <?php elseif ($pesan == 'gagal'): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-times-circle me-2"></i>
                        <strong>Gagal!</strong> Terjadi kesalahan saat memproses setor dana. Silakan coba lagi.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>

                <?php elseif ($pesan == 'input_tidak_valid'): ?>
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Perhatian!</strong> Data yang dimasukkan tidak valid. Pastikan nominal minimal Rp 10.000.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>

                <?php elseif ($pesan == 'rekening_tidak_valid'): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-times-circle me-2"></i>
                        <strong>Error!</strong> Rekening tidak valid atau tidak aktif.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>

                <?php elseif ($pesan == 'merchant_tidak_valid'): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-times-circle me-2"></i>
                        <strong>Error!</strong> Merchant tidak valid atau tidak aktif.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>

                <?php endif; ?>
            <?php endif; ?>

            <div class="row g-4">

                <!-- Form Setor Dana -->
                <div class="col-lg-5">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="mb-0 fw-bold">
                                Form Setor Dana
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if (mysqli_num_rows($result_rekening) === 0): ?>
                                <div class="alert alert-info mb-0">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Anda belum memiliki rekening aktif. Silakan buat rekening terlebih dahulu di menu
                                    <a href="../rekening/index.php" class="alert-link">Rekening</a>.
                                </div>
                            <?php else: ?>
                                <form action="proses_setor.php" method="POST" id="formSetor">

                                    <!-- Pilih Rekening -->
                                    <div class="mb-3">
                                        <label for="rekening_id" class="form-label fw-semibold">Pilih Rekening</label>
                                        <select name="rekening_id" id="rekening_id" class="form-select" required>
                                            <option value="">-- Pilih Rekening --</option>
                                            <?php while ($rek = mysqli_fetch_assoc($result_rekening)): ?>
                                                <option value="<?= $rek['id'] ?>">
                                                    <?= decrypt($rek['nomor_rekening_encrypted']) ?>
                                                    (<?= $rek['jenis_rekening'] ?>) —
                                                    Saldo: <?= formatCurrency($rek['saldo']) ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>

                                    <!-- Pilih Merchant -->
                                    <div class="mb-3">
                                        <label for="merchant_id" class="form-label fw-semibold">Merchant Penyetor</label>
                                        <select name="merchant_id" id="merchant_id" class="form-select" required>
                                            <option value="">-- Pilih Merchant --</option>
                                            <?php while ($m = mysqli_fetch_assoc($result_merchant)): ?>
                                                <option value="<?= $m['id'] ?>">
                                                    <?= htmlspecialchars($m['nama_merchant']) ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>

                                    <!-- Nominal -->
                                    <div class="mb-3">
                                        <label for="nominal" class="form-label fw-semibold">Nominal Setor (Rp)</label>
                                        <input type="number" name="nominal" id="nominal" class="form-control"
                                            min="10000" step="1000" required
                                            placeholder="Minimal Rp 10.000">
                                        <div class="form-text">Minimal setor Rp 10.000, kelipatan Rp 1.000</div>
                                    </div>

                                    <!-- Tombol Submit -->
                                    <button type="submit" class="btn btn-warning w-100 fw-bold">
                                        Setor Dana
                                    </button>

                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Riwayat Setor -->
                <div class="col-lg-7">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-light">
                            <h5 class="mb-0 fw-bold">
                                <i class="fas fa-history me-2"></i>Riwayat Setor Dana
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <?php if ($total_data === 0): ?>
                                <div class="text-center py-5 text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3"></i>
                                    <p>Belum ada riwayat setor dana.</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover table-striped mb-0">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>No</th>
                                                <th>No. Rekening</th>
                                                <th>Nominal</th>
                                                <th>Saldo Sebelum</th>
                                                <th>Saldo Sesudah</th>
                                                <th>Keterangan</th>
                                                <th>Tanggal</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $no = $offset + 1;
                                            while ($row = mysqli_fetch_assoc($result_riwayat)):
                                            ?>
                                                <tr>
                                                    <td><?= $no++ ?></td>
                                                    <td>
                                                        <code><?= decrypt($row['nomor_rekening_encrypted']) ?></code>
                                                    </td>
                                                    <td class="text-success fw-bold">
                                                        +<?= formatCurrency($row['nominal']) ?>
                                                    </td>
                                                    <td><?= formatCurrency($row['saldo_sebelum']) ?></td>
                                                    <td><?= formatCurrency($row['saldo_sesudah']) ?></td>
                                                    <td><?= htmlspecialchars($row['keterangan']) ?></td>
                                                    <td>
                                                        <small><?= formatDate($row['created_at']) ?></small>
                                                    </td>
                                                    <td>
                                                        <a href="cetak_resi.php?id=<?= $row['id'] ?>"
                                                            class="btn btn-sm btn-outline-primary"
                                                            title="Cetak Resi">
                                                            <i class="fas fa-print"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Pagination -->
                                <?php if ($total_pages > 1): ?>
                                    <div class="card-footer bg-white">
                                        <nav>
                                            <ul class="pagination pagination-sm justify-content-center mb-0">
                                                <!-- Tombol Previous -->
                                                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                                    <a class="page-link" href="?page=<?= $page - 1 ?>">
                                                        <i class="fas fa-chevron-left"></i>
                                                    </a>
                                                </li>

                                                <!-- Nomor Halaman -->
                                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                                    <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                                                    </li>
                                                <?php endfor; ?>

                                                <!-- Tombol Next -->
                                                <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                                                    <a class="page-link" href="?page=<?= $page + 1 ?>">
                                                        <i class="fas fa-chevron-right"></i>
                                                    </a>
                                                </li>
                                            </ul>
                                        </nav>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-light border-top py-3">
        <div class="container-fluid text-center">
            <small>
                &copy; <?= date('Y'); ?> Bank Multimedia
            </small>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>