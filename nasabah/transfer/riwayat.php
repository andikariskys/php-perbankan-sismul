<?php
session_start();
include "../../config/database.php";
include "../../helper/encryption.php";

/** @var mysqli $conn */

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php?pesan=belum_login');
    exit;
}

if ($_SESSION['nama_role'] !== 'Nasabah') {
    header('Location: ../../login.php?pesan=akses_ditolak');
    exit;
}

$user_id = (int) $_SESSION['user_id'];

$query_riwayat = "SELECT 
                    t.id,
                    t.nominal,
                    t.catatan,
                    t.created_at,
                    rp.id AS rekening_pengirim_id,
                    rp.nomor_rekening_encrypted AS nomor_pengirim_encrypted,
                    rt.id AS rekening_penerima_id,
                    rt.nomor_rekening_encrypted AS nomor_penerima_encrypted,
                    up.nama_lengkap AS nama_pengirim,
                    ut.nama_lengkap AS nama_penerima,
                    up.id AS user_pengirim_id,
                    ut.id AS user_penerima_id
                FROM transfer t
                JOIN rekening rp ON t.rekening_pengirim_id = rp.id
                JOIN rekening rt ON t.rekening_penerima_id = rt.id
                JOIN users up ON rp.user_id = up.id
                JOIN users ut ON rt.user_id = ut.id
                WHERE rp.user_id = ? OR rt.user_id = ?
                ORDER BY t.created_at DESC, t.id DESC";

$stmt_riwayat = mysqli_prepare($conn, $query_riwayat);
mysqli_stmt_bind_param($stmt_riwayat, "ii", $user_id, $user_id);
mysqli_stmt_execute($stmt_riwayat);
$result_riwayat = mysqli_stmt_get_result($stmt_riwayat);
$riwayat_list = [];

while ($riwayat = mysqli_fetch_assoc($result_riwayat)) {
    $riwayat['nomor_pengirim'] = decrypt($riwayat['nomor_pengirim_encrypted']);
    $riwayat['nomor_penerima'] = decrypt($riwayat['nomor_penerima_encrypted']);
    $riwayat['arah'] = ((int) $riwayat['user_pengirim_id'] === $user_id) ? 'Kirim' : 'Terima';
    $riwayat_list[] = $riwayat;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Transfer</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .hero {
            background: linear-gradient(135deg, #4f46e5, #312e81);
            color: white;
        }

        .history-card {
            border: 0;
            border-radius: 18px;
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08);
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
                    <li class="nav-item"><a class="nav-link active" href="../riwayat/index.php">Riwayat</a></li>
                    <li class="nav-item"><a class="nav-link" href="../setor/index.php">Setor</a></li>
                    <li class="nav-item"><a class="nav-link" href="../tarik/index.php">Tarik</a></li>
                    <li class="nav-item"><a class="nav-link" href="../transfer/index.php">Transfer</a></li>
                    <li class="nav-item"><a class="nav-link" href="../topup/index.php">Top Up</a></li>
                </ul>
                <a href="../../logout.php" class="btn btn-light btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <header class="hero py-5 shadow-sm">
        <div class="container">
            <h1 class="display-6 fw-bold">Riwayat Transfer</h1>
            <p class="mb-0 opacity-75">Lihat seluruh transfer masuk dan keluar Anda.</p>
        </div>
    </header>

    <main class="flex-grow-1">
        <div class="container py-4">
            <div class="d-flex gap-2 mb-3 flex-wrap">
                <a href="index.php" class="btn btn-outline-success">
                    <i class="fa-solid fa-arrow-left me-1"></i>
                    Kembali ke Transfer
                </a>
            </div>

            <div class="card history-card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Kode</th>
                                    <th>Tanggal</th>
                                    <th>Arah</th>
                                    <th>Rekening Tujuan</th>
                                    <th>Nominal</th>
                                    <th>Catatan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($riwayat_list) === 0): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">Belum ada riwayat transfer.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($riwayat_list as $riwayat): ?>
                                        <?php
                                        $kode = 'TRF-' . str_pad((string) $riwayat['id'], 6, '0', STR_PAD_LEFT);
                                        $rekening_tujuan = ($riwayat['arah'] === 'Kirim') ? $riwayat['nomor_penerima'] : $riwayat['nomor_pengirim'];
                                        ?>
                                        <tr>
                                            <td class="fw-bold"><?= htmlspecialchars($kode); ?></td>
                                            <td><?= date('d-m-Y H:i', strtotime($riwayat['created_at'])); ?></td>
                                            <td>
                                                <span class="badge <?= $riwayat['arah'] === 'Kirim' ? 'bg-danger' : 'bg-success'; ?>">
                                                    <?= htmlspecialchars($riwayat['arah']); ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars($rekening_tujuan); ?></td>
                                            <td>Rp<?= number_format((float) $riwayat['nominal'], 0, ',', '.'); ?></td>
                                            <td><?= htmlspecialchars($riwayat['catatan'] ?: '-'); ?></td>
                                            <td>
                                                <a href="cetak_resi.php?id=<?= (int) $riwayat['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="fa-solid fa-receipt me-1"></i>
                                                    Resi
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
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
