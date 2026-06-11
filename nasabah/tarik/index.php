<?php
session_start();
include "../../config/database.php";
require_once "../../helper/format.php";
require_once "../../helper/encryption.php";

if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php?pesan=belum_login');
    exit;
}

if ($_SESSION['nama_role'] !== 'Nasabah') {
    header('Location: /login.php?pesan=akses_ditolak');
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil data rekening dari database
$query_rekening = "SELECT id, nomor_rekening_encrypted, saldo FROM rekening WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $query_rekening);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$rekening = mysqli_fetch_assoc($result);

$rekening_id = $rekening['id'];
// Menggunakan helper decrypt() dari Anggota 3
$no_rekening_asli = decrypt($rekening['nomor_rekening_encrypted']);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tarik Tunai - Bank Multimedia</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .hero {
            background: linear-gradient(135deg, #d63384, #7a1f4d);
            color: white;
        }
    </style>
</head>

<body class="d-flex flex-column min-vh-100 bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-success">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="#">Bank Multimedia</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNasabah">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNasabah">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="../dashboard/index.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="../profil/index.php">Profil</a></li>
                    <li class="nav-item"><a class="nav-link" href="../rekening/index.php">Rekening</a></li>
                    <li class="nav-item"><a class="nav-link" href="../riwayat/index.php">Riwayat</a></li>
                    <li class="nav-item"><a class="nav-link" href="../setor/index.php">Setor</a></li>
                    <li class="nav-item"><a class="nav-link active" href="../tarik/index.php">Tarik</a></li>
                    <li class="nav-item"><a class="nav-link" href="../transfer/index.php">Transfer</a></li>
                    <li class="nav-item"><a class="nav-link" href="../topup/index.php">Top Up</a></li>
                </ul>
                <a href="../../logout.php" class="btn btn-light btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <header class="hero py-5 shadow-sm">
        <div class="container">
            <h1 class="display-6 fw-bold">Tarik Tunai</h1>
            <p class="mb-0 opacity-75">Lakukan penarikan saldo dari rekening Anda.</p>
        </div>
    </header>

    <main class="flex-grow-1">
        <div class="container py-4">

            <div class="row">
                <div class="col-lg-4 mb-4">
                    
                    <?php if(isset($_SESSION['pesan_error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i><?= $_SESSION['pesan_error']; unset($_SESSION['pesan_error']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if(isset($_SESSION['pesan_sukses'])): ?>
                        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                            <i class="fas fa-check-circle me-2"></i><?= $_SESSION['pesan_sukses']; unset($_SESSION['pesan_sukses']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <div class="card shadow-sm mb-4 border-0">
                        <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
                            <h5 class="fw-bold"><i class="fas fa-wallet text-success me-2"></i>Informasi Rekening</h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-1 small">Nama Nasabah</p>
                            <p class="fw-bold mb-3"><?= htmlspecialchars($_SESSION['nama_lengkap']); ?></p>
                            
                            <p class="text-muted mb-1 small">Nomor Rekening</p>
                            <p class="fw-bold mb-3"><?= htmlspecialchars($no_rekening_asli); ?></p>
                            
                            <p class="text-muted mb-1 small">Total Saldo Aktif</p>
                            <h3 class="text-success fw-bold"><?= formatCurrency($rekening['saldo']); ?></h3>
                        </div>
                    </div>

                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
                            <h5 class="fw-bold"><i class="fas fa-money-bill-wave text-primary me-2"></i>Form Penarikan</h5>
                        </div>
                        <div class="card-body">
                            <form action="proses_tarik.php" method="POST">
                                <div class="mb-3">
                                    <label for="nominal" class="form-label text-muted small">Nominal Tarik Tunai (Rp)</label>
                                    <input type="number" class="form-control form-control-lg" id="nominal" name="nominal" required min="10000" placeholder="Contoh: 50000">
                                    <small class="text-muted">Minimum penarikan Rp 10.000</small>
                                </div>
                                <div class="mb-4">
                                    <label for="password" class="form-label text-muted small">Password Akun</label>
                                    <input type="password" class="form-control form-control-lg" id="password" name="password" required placeholder="Masukkan password Anda">
                                </div>
                                <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">
                                    <i class="fas fa-hand-holding-usd me-2"></i>Tarik Dana Sekarang
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white border-bottom-0 pt-3 pb-0 d-flex justify-content-between align-items-center">
                            <h5 class="fw-bold"><i class="fas fa-history text-secondary me-2"></i>Riwayat Tarik Tunai</h5>
                        </div>
                        <div class="card-body p-0 mt-3">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0 text-center">
                                    <thead class="table-light">
                                        <tr>
                                            <th>No</th>
                                            <th>Waktu Transaksi</th>
                                            <th>Nominal</th>
                                            <th>Sisa Saldo</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $jenis_tx = 'TARIK';
                                        $query_riwayat = "SELECT id, nominal, saldo_sesudah, created_at FROM transaksi WHERE rekening_id = ? AND jenis_transaksi = ? ORDER BY created_at DESC";
                                        $stmt_riwayat = mysqli_prepare($conn, $query_riwayat);
                                        mysqli_stmt_bind_param($stmt_riwayat, "is", $rekening_id, $jenis_tx);
                                        mysqli_stmt_execute($stmt_riwayat);
                                        $result_riwayat = mysqli_stmt_get_result($stmt_riwayat);
                                        
                                        $no = 1;
                                        if(mysqli_num_rows($result_riwayat) > 0):
                                            while($row = mysqli_fetch_assoc($result_riwayat)):
                                        ?>
                                        <tr>
                                            <td><?= $no++; ?></td>
                                            <td><?= ucwords(formatDate($row['created_at'])) . date(', H:i', strtotime($row['created_at'])); ?></td>
                                            <td class="text-danger fw-bold">- <?= formatCurrency($row['nominal']); ?></td>
                                            <td><?= formatCurrency($row['saldo_sesudah']); ?></td>
                                            <td>
                                                <a href="cetak_resi.php?id=<?= $row['id']; ?>" target="_blank" class="btn btn-sm btn-outline-success">
                                                    <i class="fas fa-print"></i> Resi
                                                </a>
                                            </td>
                                        </tr>
                                        <?php 
                                            endwhile;
                                        else: 
                                        ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">Belum ada aktivitas tarik tunai.</td>
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

    <footer class="bg-light border-top py-3 mt-auto">
        <div class="container text-center">
            <small class="text-muted">
                © <?= date('Y'); ?> Bank Multimedia - Kelompok 3
            </small>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>