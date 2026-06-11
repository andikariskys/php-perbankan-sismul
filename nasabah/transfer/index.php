<?php
session_start();
include "../../config/database.php";
include "../../helper/encryption.php";

/** @var mysqli $conn */

$user_id = $_SESSION['user_id'] ?? 0;
$rekening_list = [];

if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php?pesan=belum_login');
    exit;
}

if ($_SESSION['nama_role'] !== 'Nasabah') {
    header('Location: /login.php?pesan=akses_ditolak');
    exit;
}

$query_rekening = "SELECT id, nomor_rekening_encrypted, saldo, jenis_rekening FROM rekening WHERE user_id = ? AND status_rekening = 'Aktif' ORDER BY id DESC";
$stmt_rekening = mysqli_prepare($conn, $query_rekening);
mysqli_stmt_bind_param($stmt_rekening, "i", $user_id);
mysqli_stmt_execute($stmt_rekening);
$result_rekening = mysqli_stmt_get_result($stmt_rekening);

while ($rekening = mysqli_fetch_assoc($result_rekening)) {
    $rekening['nomor_rekening'] = decrypt($rekening['nomor_rekening_encrypted']);
    $rekening_list[] = $rekening;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transfer Dana</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .hero {
            background: linear-gradient(135deg, #4f46e5, #312e81);
            color: white;
        }

        .transfer-card {
            border: 0;
            border-radius: 18px;
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08);
        }

        .helper-panel {
            background: linear-gradient(135deg, #eef2ff, #ecfeff);
            border: 1px solid rgba(79, 70, 229, 0.12);
            border-radius: 16px;
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
                        <a class="nav-link active"
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
                Transfer Dana
            </h1>
            <p class="mb-0 opacity-75">
                Kirim uang ke sesama nasabah Bank Multimedia.
            </p>
        </div>
    </header>

    <main class="flex-grow-1">
        <div class="container-fluid py-4">

            <div class="row justify-content-center g-4">
                <div class="col-lg-8">

                    <div class="mb-3 d-flex gap-2 flex-wrap">
                        <a href="riwayat.php" class="btn btn-outline-success">
                            <i class="fa-solid fa-clock-rotate-left me-1"></i>
                            Riwayat Transfer
                        </a>
                    </div>

                    <?php if (isset($_GET['pesan'])): ?>
                        <?php
                        $pesan = htmlspecialchars($_GET['pesan']);
                        $alert_type = 'danger';
                        $alert_text = 'Terjadi kesalahan saat memproses transfer.';

                        switch ($pesan) {
                            case 'rekening_tidak_ada':
                                $alert_text = 'Anda belum memiliki rekening aktif.';
                                break;
                            case 'rekening_pengirim_tidak_valid':
                                $alert_text = 'Rekening pengirim tidak valid.';
                                break;
                            case 'rekening_tujuan_tidak_valid':
                                $alert_text = 'Nomor rekening tujuan tidak ditemukan atau tidak aktif.';
                                break;
                            case 'rekening_sama':
                                $alert_text = 'Rekening tujuan tidak boleh sama dengan rekening pengirim.';
                                break;
                            case 'nominal_tidak_valid':
                                $alert_text = 'Nominal transfer tidak valid.';
                                break;
                            case 'saldo_kurang':
                                $alert_text = 'Saldo rekening tidak mencukupi.';
                                break;
                            case 'gagal':
                                $alert_text = 'Transfer gagal diproses. Silakan coba lagi.';
                                break;
                            case 'berhasil':
                                $alert_type = 'success';
                                $alert_text = 'Transfer berhasil diproses.';
                                break;
                        }
                        ?>

                        <div class="alert alert-<?= $alert_type ?> alert-dismissible fade show shadow-sm" role="alert">
                            <?= htmlspecialchars($alert_text); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <div class="row g-4">
                        <div class="col-md-5">
                            <div class="helper-panel p-4 h-100">
                                <h5 class="fw-bold mb-3">
                                    <i class="fa-solid fa-circle-info text-primary me-2"></i>
                                    Validasi Rekening
                                </h5>
                                <p class="mb-3 text-muted">
                                    Pastikan nomor rekening tujuan benar sebelum transfer diproses.
                                </p>
                                <ul class="mb-0 ps-3 text-muted">
                                    <li>Transfer hanya untuk rekening aktif.</li>
                                    <li>Nomor rekening tujuan akan diverifikasi otomatis.</li>
                                    <li>Riwayat transaksi dapat dilihat setelah transfer berhasil.</li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-md-7">
                            <div class="card transfer-card">
                                <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                                    <h4 class="fw-bold mb-1">Transfer Dana</h4>
                                    <p class="text-muted mb-0">Kirim dana ke rekening tujuan dalam bank yang sama.</p>
                                </div>

                                <div class="card-body p-4">
                                    <?php if (count($rekening_list) === 0): ?>
                                        <div class="alert alert-warning mb-0">
                                            Anda belum memiliki rekening aktif untuk digunakan transfer.
                                        </div>
                                    <?php else: ?>
                                        <form action="proses_transfer.php" method="POST">
                                            <div class="mb-3">
                                                <label class="form-label">Rekening Pengirim</label>
                                                <select name="rekening_pengirim_id" class="form-select" required>
                                                    <option value="">-- Pilih Rekening --</option>
                                                    <?php foreach ($rekening_list as $rekening): ?>
                                                        <option value="<?= (int) $rekening['id']; ?>">
                                                            <?= htmlspecialchars($rekening['nomor_rekening']); ?> (<?= htmlspecialchars($rekening['jenis_rekening']); ?>) - Saldo Rp<?= number_format((float) $rekening['saldo'], 0, ',', '.'); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Nomor Rekening Tujuan</label>
                                                <input type="text" name="nomor_rekening_tujuan" class="form-control" placeholder="Contoh: 0261234567890" required>
                                                <small class="text-muted">Nomor rekening akan divalidasi otomatis saat transfer.</small>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Nominal Transfer</label>
                                                <input type="number" name="nominal" class="form-control" min="1" step="0.01" placeholder="Masukkan nominal transfer" required>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Catatan</label>
                                                <textarea name="catatan" class="form-control" rows="3" maxlength="255" placeholder="Opsional"></textarea>
                                            </div>

                                            <button type="submit" name="transfer_submit" class="btn btn-success w-100">
                                                <i class="fa-solid fa-paper-plane me-1"></i>
                                                Proses Transfer
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
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
                © <?= date('Y'); ?> Bank Multimedia
            </small>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>