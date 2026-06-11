<?php
session_start();
include "../../config/database.php";
include "../../helper/encryption.php";
include "../../helper/format.php";

if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php?pesan=belum_login');
    exit;
}

if ($_SESSION['nama_role'] !== 'Nasabah') {
    header('Location: /login.php?pesan=akses_ditolak');
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'transaksi';

// Proses Aksi Notifikasi
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    if ($action === 'read' && isset($_GET['id'])) {
        $notif_id = (int)$_GET['id'];
        $query_read = "UPDATE notifikasi SET status_baca = 'Dibaca' WHERE id = ? AND user_id = ?";
        $stmt_read = mysqli_prepare($conn, $query_read);
        mysqli_stmt_bind_param($stmt_read, "ii", $notif_id, $user_id);
        mysqli_stmt_execute($stmt_read);
        mysqli_stmt_close($stmt_read);
        header("Location: index.php?tab=notifikasi&pesan=read_success");
        exit;
    } elseif ($action === 'read_all') {
        $query_read_all = "UPDATE notifikasi SET status_baca = 'Dibaca' WHERE user_id = ?";
        $stmt_read_all = mysqli_prepare($conn, $query_read_all);
        mysqli_stmt_bind_param($stmt_read_all, "i", $user_id);
        mysqli_stmt_execute($stmt_read_all);
        mysqli_stmt_close($stmt_read_all);
        header("Location: index.php?tab=notifikasi&pesan=read_all_success");
        exit;
    } elseif ($action === 'delete' && isset($_GET['id'])) {
        $notif_id = (int)$_GET['id'];
        $query_del = "DELETE FROM notifikasi WHERE id = ? AND user_id = ?";
        $stmt_del = mysqli_prepare($conn, $query_del);
        mysqli_stmt_bind_param($stmt_del, "ii", $notif_id, $user_id);
        mysqli_stmt_execute($stmt_del);
        mysqli_stmt_close($stmt_del);
        header("Location: index.php?tab=notifikasi&pesan=delete_success");
        exit;
    }
}

// Ambil jumlah notifikasi belum dibaca
$query_unread = "SELECT COUNT(*) as count FROM notifikasi WHERE user_id = ? AND status_baca = 'Belum Dibaca'";
$stmt_unread = mysqli_prepare($conn, $query_unread);
mysqli_stmt_bind_param($stmt_unread, "i", $user_id);
mysqli_stmt_execute($stmt_unread);
$result_unread = mysqli_stmt_get_result($stmt_unread);
$unread_count = mysqli_fetch_assoc($result_unread)['count'];
mysqli_stmt_close($stmt_unread);

// Filter Transaksi
$filter_type = isset($_GET['filter_type']) ? $_GET['filter_type'] : '';
$where_clause = "";
if (!empty($filter_type)) {
    if ($filter_type === 'TRANSFER') {
        $where_clause = "AND (t.jenis_transaksi = 'TRANSFER_KELUAR' OR t.jenis_transaksi = 'TRANSFER_MASUK')";
    } else {
        $where_clause = "AND t.jenis_transaksi = '" . mysqli_real_escape_string($conn, $filter_type) . "'";
    }
}

// Fetch Transaksi
$query_transaksi = "SELECT t.*, r.nomor_rekening_encrypted, r.jenis_rekening
                    FROM transaksi t
                    JOIN rekening r ON t.rekening_id = r.id
                    WHERE r.user_id = ? $where_clause
                    ORDER BY t.created_at DESC";
$stmt_trans = mysqli_prepare($conn, $query_transaksi);
mysqli_stmt_bind_param($stmt_trans, "i", $user_id);
mysqli_stmt_execute($stmt_trans);
$transaksi_data = mysqli_stmt_get_result($stmt_trans);
mysqli_stmt_close($stmt_trans);

// Fetch Audit Log
$query_audit = "SELECT * FROM audit_log WHERE user_id = ? ORDER BY created_at DESC";
$stmt_aud = mysqli_prepare($conn, $query_audit);
mysqli_stmt_bind_param($stmt_aud, "i", $user_id);
mysqli_stmt_execute($stmt_aud);
$audit_data = mysqli_stmt_get_result($stmt_aud);
mysqli_stmt_close($stmt_aud);

// Fetch Notifikasi
$query_notif = "SELECT * FROM notifikasi WHERE user_id = ? ORDER BY created_at DESC";
$stmt_not = mysqli_prepare($conn, $query_notif);
mysqli_stmt_bind_param($stmt_not, "i", $user_id);
mysqli_stmt_execute($stmt_not);
$notifikasi_data = mysqli_stmt_get_result($stmt_not);
mysqli_stmt_close($stmt_not);

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Transaksi</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .hero {
            background:  #198754;
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

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNasabah">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNasabah">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../dashboard/index.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../profil/index.php">Profil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../rekening/index.php">Rekening</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="../riwayat/index.php">Riwayat</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../setor/index.php">Setor</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../tarik/index.php">Tarik</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../transfer/index.php">Transfer</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../topup/index.php">Top Up</a>
                    </li>
                </ul>
                <a href="../../logout.php" class="btn btn-light btn-sm">
                    Logout
                </a>
            </div>
        </div>
    </nav>

    <!-- Header -->
    <header class="hero py-5 shadow-sm">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="display-6 fw-bold">Riwayat Transaksi</h1>
                    <p class="mb-0 opacity-75">Lihat semua jejak transaksi yang pernah Anda lakukan.</p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <div class="btn-group">
                        <a href="ekspor.php?type=<?= ($active_tab === 'audit' || $active_tab === 'notifikasi') ? $active_tab : 'transaksi' ?>" id="btn-export" class="btn btn-light btn-sm fw-semibold">
                            Ekspor CSV
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Workspace -->
    <main class="flex-grow-1">
        <div class="container py-4">

            <?php if (isset($_GET['pesan'])): ?>
                <?php if ($_GET['pesan'] === 'read_success'): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        Notifikasi berhasil ditandai telah dibaca.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php elseif ($_GET['pesan'] === 'read_all_success'): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        Semua notifikasi berhasil ditandai telah dibaca.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php elseif ($_GET['pesan'] === 'delete_success'): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        Notifikasi berhasil dihapus.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <div class="card p-4">
                <!-- Tab Menu -->
                <ul class="nav nav-tabs mb-4" id="riwayatTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?= $active_tab === 'transaksi' ? 'active' : '' ?>" id="transaksi-tab" data-bs-toggle="tab" data-bs-target="#transaksi-tab-pane" type="button" role="tab" aria-controls="transaksi-tab-pane" aria-selected="<?= $active_tab === 'transaksi' ? 'true' : 'false' ?>">
                            Riwayat Transaksi
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?= $active_tab === 'audit' ? 'active' : '' ?>" id="audit-tab" data-bs-toggle="tab" data-bs-target="#audit-tab-pane" type="button" role="tab" aria-controls="audit-tab-pane" aria-selected="<?= $active_tab === 'audit' ? 'true' : 'false' ?>">
                            Log Aktivitas
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?= $active_tab === 'notifikasi' ? 'active' : '' ?>" id="notifikasi-tab" data-bs-toggle="tab" data-bs-target="#notifikasi-tab-pane" type="button" role="tab" aria-controls="notifikasi-tab-pane" aria-selected="<?= $active_tab === 'notifikasi' ? 'true' : 'false' ?>">
                            Notifikasi Sistem
                            <?php if ($unread_count > 0): ?>
                                <span class="badge rounded-pill bg-danger ms-1"><?= $unread_count ?></span>
                            <?php endif; ?>
                        </button>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content" id="riwayatTabContent">
                    
                    <!-- TAB 1: RIWAYAT TRANSAKSI -->
                    <div class="tab-pane fade <?= $active_tab === 'transaksi' ? 'show active' : '' ?>" id="transaksi-tab-pane" role="tabpanel" aria-labelledby="transaksi-tab" tabindex="0">
                        
                        <!-- Filter & Search -->
                        <div class="row g-3 mb-4 align-items-center">
                            <div class="col-md-4">
                                <form method="GET" action="index.php" id="filter-form">
                                    <input type="hidden" name="tab" value="transaksi">
                                    <label class="form-label fw-semibold small text-muted">Filter Jenis Transaksi</label>
                                    <select class="form-select" name="filter_type" onchange="document.getElementById('filter-form').submit()">
                                        <option value="">Semua Transaksi</option>
                                        <option value="SETOR" <?= $filter_type === 'SETOR' ? 'selected' : '' ?>>Setor Tunai</option>
                                        <option value="TARIK" <?= $filter_type === 'TARIK' ? 'selected' : '' ?>>Tarik Tunai</option>
                                        <option value="TRANSFER" <?= $filter_type === 'TRANSFER' ? 'selected' : '' ?>>Transfer (Masuk/Keluar)</option>
                                        <option value="TOPUP_EWALLET" <?= $filter_type === 'TOPUP_EWALLET' ? 'selected' : '' ?>>Top Up E-Wallet</option>
                                    </select>
                                </form>
                            </div>
                        </div>

                        <!-- Tabel Transaksi -->
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>No</th>
                                        <th>Tanggal & Waktu</th>
                                        <th>No. Rekening</th>
                                        <th>Jenis</th>
                                        <th>Transaksi</th>
                                        <th class="text-end">Nominal</th>
                                        <th>Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (mysqli_num_rows($transaksi_data) > 0): ?>
                                        <?php 
                                        $no = 1;
                                        while ($row = mysqli_fetch_assoc($transaksi_data)): 
                                            $nomor_rek = decrypt($row['nomor_rekening_encrypted']);
                                            $jenis_t = $row['jenis_transaksi'];
                                            
                                            // Set Badge Style
                                            $badge_class = 'bg-secondary';
                                            $type_text = $jenis_t;
                                            $amount_prefix = '';
                                            $amount_class = 'fw-bold';
                                            
                                            if ($jenis_t === 'SETOR') {
                                                $badge_class = 'bg-success';
                                                $type_text = 'Setor Tunai';
                                                $amount_prefix = '+ ';
                                                $amount_class .= ' text-success';
                                            } elseif ($jenis_t === 'TARIK') {
                                                $badge_class = 'bg-danger';
                                                $type_text = 'Tarik Tunai';
                                                $amount_prefix = '- ';
                                                $amount_class .= ' text-danger';
                                            } elseif ($jenis_t === 'TRANSFER_KELUAR') {
                                                $badge_class = 'bg-warning text-dark';
                                                $type_text = 'Transfer Keluar';
                                                $amount_prefix = '- ';
                                                $amount_class .= ' text-warning';
                                            } elseif ($jenis_t === 'TRANSFER_MASUK') {
                                                $badge_class = 'bg-primary';
                                                $type_text = 'Transfer Masuk';
                                                $amount_prefix = '+ ';
                                                $amount_class .= ' text-primary';
                                            } elseif ($jenis_t === 'TOPUP_EWALLET') {
                                                $badge_class = 'bg-info text-dark';
                                                $type_text = 'Top Up E-Wallet';
                                                $amount_prefix = '- ';
                                                $amount_class .= ' text-info';
                                            }
                                        ?>
                                            <tr>
                                                <td><?= $no++; ?></td>
                                                <td><small class="text-muted"><?= date('d-m-y H:i:s', strtotime($row['created_at'])); ?></small></td>
                                                <td><code><?= htmlspecialchars($nomor_rek); ?></code></td>
                                                <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($row['jenis_rekening']); ?></span></td>
                                                <td><span class="badge <?= $badge_class; ?>"><?= $type_text; ?></span></td>
                                                <td class="text-end <?= $amount_class; ?>"><?= $amount_prefix . formatCurrency($row['nominal']); ?></td>
                                                <td><span class="small text-muted"><?= htmlspecialchars($row['keterangan']); ?></span></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-4 text-muted">
                                                Tidak ada riwayat transaksi ditemukan.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                    </div>

                    <!-- TAB 2: LOG AKTIVITAS PENGGUNA -->
                    <div class="tab-pane fade <?= $active_tab === 'audit' ? 'show active' : '' ?>" id="audit-tab-pane" role="tabpanel" aria-labelledby="audit-tab" tabindex="0">
                        
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>No</th>
                                        <th>Waktu</th>
                                        <th>Aktivitas</th>
                                        <th>Deskripsi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (mysqli_num_rows($audit_data) > 0): ?>
                                        <?php 
                                        $no = 1;
                                        while ($row = mysqli_fetch_assoc($audit_data)): 
                                        ?>
                                            <tr>
                                                <td><?= $no++; ?></td>
                                                <td><small class="text-muted"><?= date('d-m-y H:i:s', strtotime($row['created_at'])); ?></small></td>
                                                <td><span class="fw-semibold text-dark"><?= htmlspecialchars($row['aktivitas']); ?></span></td>
                                                <td><span class="text-muted small"><?= htmlspecialchars($row['deskripsi']); ?></span></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-muted">
                                                Tidak ada log aktivitas audit.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                    </div>

                    <!-- TAB 3: NOTIFIKASI SISTEM -->
                    <div class="tab-pane fade <?= $active_tab === 'notifikasi' ? 'show active' : '' ?>" id="notifikasi-tab-pane" role="tabpanel" aria-labelledby="notifikasi-tab" tabindex="0">
                        
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h6 class="mb-0 text-muted fw-semibold">Pemberitahuan & Notifikasi Transaksi</h6>
                            <?php if ($unread_count > 0): ?>
                                <a href="index.php?action=read_all" class="btn btn-outline-success btn-sm rounded-pill">
                                    Tandai Semua Dibaca
                                </a>
                            <?php endif; ?>
                        </div>

                        <div class="list-group list-group-flush">
                            <?php if (mysqli_num_rows($notifikasi_data) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($notifikasi_data)): ?>
                                    <div class="list-group-item list-group-item-action py-3 px-3 mb-2 rounded border <?= $row['status_baca'] === 'Belum Dibaca' ? 'bg-light border-success' : 'bg-light' ?>">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="me-3">
                                                <div class="d-flex align-items-center mb-1">
                                                    <span class="fw-bold text-dark me-2"><?= htmlspecialchars($row['judul']); ?></span>
                                                    <?php if ($row['status_baca'] === 'Belum Dibaca'): ?>
                                                        <span class="badge bg-success rounded-pill" style="font-size: 0.7rem;">Baru</span>
                                                    <?php endif; ?>
                                                </div>
                                                <p class="mb-1 text-secondary small"><?= htmlspecialchars($row['pesan']); ?></p>
                                                <small class="text-muted"><?= date('d-m-y H:i:s', strtotime($row['created_at'])); ?></small>
                                            </div>
                                            
                                            <!-- Aksi Notif -->
                                            <div class="btn-group">
                                                <?php if ($row['status_baca'] === 'Belum Dibaca'): ?>
                                                    <a href="index.php?action=read&id=<?= $row['id']; ?>" class="btn btn-outline-success btn-sm py-1 px-2" title="Tandai telah dibaca">
                                                        <i class="fas fa-check"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <a href="index.php?action=delete&id=<?= $row['id']; ?>" class="btn btn-outline-danger btn-sm py-1 px-2" title="Hapus" onclick="return confirm('Apakah Anda yakin ingin menghapus notifikasi ini?')">
                                                    <i class="fas fa-trash-alt"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div class="text-center py-4 text-muted">
                                    Tidak ada notifikasi sistem masuk.
                                </div>
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
            <small class="text-muted">
                © <?= date('Y'); ?> Bank Multimedia | Kelompok 3 - Sistem Multimedia
            </small>
        </div>
    </footer>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Update tautan tombol Ekspor berdasarkan tab aktif secara dinamis
        document.addEventListener('DOMContentLoaded', function() {
            var triggerTabList = [].slice.call(document.querySelectorAll('#riwayatTab button'))
            var exportBtn = document.getElementById('btn-export');
            
            triggerTabList.forEach(function(tabEl) {
                tabEl.addEventListener('shown.bs.tab', function(event) {
                    var tabId = event.target.id;
                    if (tabId === 'transaksi-tab') {
                        exportBtn.setAttribute('href', 'ekspor.php?type=transaksi');
                    } else if (tabId === 'audit-tab') {
                        exportBtn.setAttribute('href', 'ekspor.php?type=audit');
                    } else if (tabId === 'notifikasi-tab') {
                        exportBtn.setAttribute('href', 'ekspor.php?type=notifikasi');
                    }
                });
            });
        });
    </script>
</body>

</html>