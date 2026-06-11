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

$query_provider = "SELECT * FROM provider_ewallet WHERE nama_provider IN ('OVO', 'DANA', 'GoPay', 'ShopeePay')";
$result_provider = mysqli_query($conn, $query_provider);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Top Up E-Wallet</title>

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
            <h1 class="display-6 fw-bold">Top Up E-Wallet</h1>
            <p class="mb-0 opacity-75">Isi ulang saldo OVO, DANA, GoPay, dan ShopeePay.</p>
        </div>
    </header>

    <main class="flex-grow-1">
        <div class="container-fluid py-4">

            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">

                    <div class="mb-3">
                        <a href="riwayat.php" class="btn btn-outline-success">
                            <i class="fa-solid fa-clock-rotate-left me-1"></i>
                            Riwayat Top Up
                        </a>
                    </div>

                    <?php if (isset($_GET['pesan'])): ?>
                        <?php if ($_GET['pesan'] == 'saldo_kurang'): ?>
                            <div class="alert alert-danger">Saldo rekening tidak mencukupi.</div>
                        <?php elseif ($_GET['pesan'] == 'rekening_tidak_ada'): ?>
                            <div class="alert alert-danger">Rekening tidak ditemukan.</div>
                        <?php elseif ($_GET['pesan'] == 'provider_tidak_valid'): ?>
                            <div class="alert alert-danger">Provider e-wallet tidak valid.</div>
                        <?php elseif ($_GET['pesan'] == 'nominal_tidak_valid'): ?>
                            <div class="alert alert-danger">Nominal top up tidak valid. Minimal Rp10.000.</div>
                        <?php elseif ($_GET['pesan'] == 'nomor_tidak_valid'): ?>
                            <div class="alert alert-danger">Nomor tujuan tidak valid.</div>
                        <?php else: ?>
                            <div class="alert alert-danger">Terjadi kesalahan saat memproses top up.</div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">
                                <i class="fa-solid fa-wallet me-2"></i>
                                Form Top Up E-Wallet
                            </h5>
                        </div>

                        <div class="card-body">
                            <form action="proses_topup.php" method="POST">

                                <div class="mb-3">
                                    <label class="form-label">Provider E-Wallet</label>
                                    <select name="provider_id" class="form-select" required>
                                        <option value="">-- Pilih Provider --</option>

                                        <?php while ($provider = mysqli_fetch_assoc($result_provider)): ?>
                                            <option value="<?= $provider['id']; ?>">
                                                <?= htmlspecialchars($provider['nama_provider']); ?>
                                                - Fee Rp<?= number_format($provider['biaya_admin'], 0, ',', '.'); ?>
                                            </option>
                                        <?php endwhile; ?>

                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Nomor Tujuan</label>
                                    <input type="text"
                                           name="nomor_tujuan"
                                           class="form-control"
                                           placeholder="Contoh: 081234567890"
                                           required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Nominal Top Up</label>
                                    <input type="number"
                                           name="nominal"
                                           class="form-control"
                                           min="10000"
                                           placeholder="Minimal Rp10.000"
                                           required>
                                    <small class="text-muted">
                                        Total potongan = nominal top up + fee provider.
                                    </small>
                                </div>

                                <button type="submit" name="topup_submit" class="btn btn-success w-100">
                                    <i class="fa-solid fa-paper-plane me-1"></i>
                                    Proses Top Up
                                </button>

                            </form>
                        </div>
                    </div>

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