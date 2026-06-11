<?php
session_start();
include "../../config/database.php";

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php?pesan=belum_login');
    exit;
}

if ($_SESSION['nama_role'] !== 'Nasabah') {
    header('Location: ../../login.php?pesan=akses_ditolak');
    exit;
}

$user_id = $_SESSION['user_id'];

$query_riwayat = "SELECT 
                    te.id,
                    te.nomor_tujuan,
                    te.nominal,
                    te.biaya_admin,
                    te.total_potongan,
                    te.created_at,
                    pe.nama_provider
                  FROM topup_ewallet te
                  JOIN provider_ewallet pe ON te.provider_id = pe.id
                  JOIN rekening r ON te.rekening_id = r.id
                  WHERE r.user_id = ?
                  ORDER BY te.created_at DESC";

$stmt_riwayat = mysqli_prepare($conn, $query_riwayat);
mysqli_stmt_bind_param($stmt_riwayat, "i", $user_id);
mysqli_stmt_execute($stmt_riwayat);
$result_riwayat = mysqli_stmt_get_result($stmt_riwayat);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Top Up</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        .hero {
            background: linear-gradient(135deg, #20c997, #0f766e);
            color: white;
        }
    </style>
</head>

<body class="d-flex flex-column min-vh-100">

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
                    <li class="nav-item"><a class="nav-link" href="../tarik/index.php">Tarik</a></li>
                    <li class="nav-item"><a class="nav-link" href="../transfer/index.php">Transfer</a></li>
                    <li class="nav-item"><a class="nav-link active" href="../topup/index.php">Top Up</a></li>
                </ul>

                <a href="../../logout.php" class="btn btn-light btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <header class="hero py-5 shadow-sm">
        <div class="container">
            <h1 class="display-6 fw-bold">Riwayat Top Up</h1>
            <p class="mb-0 opacity-75">Daftar transaksi top up e-wallet Anda.</p>
        </div>
    </header>

    <main class="flex-grow-1">
        <div class="container-fluid py-4">

            <div class="mb-3">
                <a href="index.php" class="btn btn-outline-success">
                    <i class="fa-solid fa-plus me-1"></i>
                    Top Up Baru
                </a>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fa-solid fa-clock-rotate-left me-2"></i>
                        Riwayat Top Up E-Wallet
                    </h5>
                </div>

                <div class="card-body table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-success">
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Provider</th>
                                <th>Nomor Tujuan</th>
                                <th>Nominal</th>
                                <th>Fee</th>
                                <th>Total Potongan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($result_riwayat) > 0): ?>
                                <?php $no = 1; ?>
                                <?php while ($row = mysqli_fetch_assoc($result_riwayat)): ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><?= date('d-m-Y H:i', strtotime($row['created_at'])); ?></td>
                                        <td><?= htmlspecialchars($row['nama_provider']); ?></td>
                                        <td><?= htmlspecialchars($row['nomor_tujuan']); ?></td>
                                        <td>Rp<?= number_format($row['nominal'], 0, ',', '.'); ?></td>
                                        <td>Rp<?= number_format($row['biaya_admin'], 0, ',', '.'); ?></td>
                                        <td>Rp<?= number_format($row['total_potongan'], 0, ',', '.'); ?></td>
                                        <td>
                                            <a href="cetak_resi.php?id=<?= $row['id']; ?>" class="btn btn-sm btn-success">
                                                <i class="fa-solid fa-print me-1"></i>
                                                Resi
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted">
                                        Belum ada riwayat top up.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>

    <footer class="bg-light border-top py-3">
        <div class="container-fluid text-center">
            <small>© <?= date('Y'); ?> Bank Multimedia</small>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>