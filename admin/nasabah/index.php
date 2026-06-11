<?php
session_start();
include "../../config/database.php";
include "../helpers/admin_auth.php";
include "../helpers/pagination.php";
include "../models/NasabahModel.php";
include "../../helper/format.php";
include "../../helper/encryption.php";

adminGuard();

// Catat audit log: admin melihat data nasabah
catatAuditAdmin($conn, $_SESSION['user_id'], $_SESSION['user_id'], 'Lihat Data Nasabah', 'Admin mengakses halaman kelola nasabah');

// Parameter filter/search
$search   = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter   = isset($_GET['filter']) ? trim($_GET['filter']) : '';
$page     = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$per_page = 10;

// Detail nasabah
$detail = null;
$detail_rekening = [];
if (isset($_GET['detail'])) {
    $detail_id = (int) $_GET['detail'];
    $detail = getNasabahById($conn, $detail_id);
    if ($detail) {
        include "../models/RekeningModel.php";
        $detail_rekening = getRekeningByUserId($conn, $detail_id);
    }
}

// Ambil data nasabah dengan search, pagination
$nasabah_data = getNasabahList($conn, $search, $page, $per_page);

// Filter status setelah fetch (jika filter diberikan)
if (!empty($filter) && in_array($filter, ['Pending', 'Aktif', 'Nonaktif'])) {
    // Re-query with status filter
    $where_extra = "WHERE u.role_id = 2 AND u.status_akun = ?";
    $params = [$filter];
    $types = 's';

    if (!empty($search)) {
        $search_like = "%{$search}%";
        $where_extra .= " AND (u.nama_lengkap LIKE ? OR u.email LIKE ? OR u.no_hp LIKE ?)";
        $params[] = $search_like;
        $params[] = $search_like;
        $params[] = $search_like;
        $types .= 'sss';
    }

    $offset = ($page - 1) * $per_page;

    // Count
    $count_sql = "SELECT COUNT(*) AS total FROM users u $where_extra";
    $stmt = mysqli_prepare($conn, $count_sql);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $total = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['total'];
    mysqli_stmt_close($stmt);

    // Fetch
    $sql = "SELECT u.*,
                   (SELECT COUNT(*) FROM rekening r WHERE r.user_id = u.id) AS jumlah_rekening,
                   (SELECT COALESCE(SUM(r2.saldo), 0) FROM rekening r2 WHERE r2.user_id = u.id) AS total_saldo
            FROM users u
            $where_extra
            ORDER BY u.created_at DESC
            LIMIT ? OFFSET ?";
    $params[] = $per_page;
    $params[] = $offset;
    $types .= 'ii';

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    mysqli_stmt_close($stmt);

    $nasabah_data = ['data' => $rows, 'total' => (int) $total];
}

$nasabah_list = $nasabah_data['data'];
$total_rows = $nasabah_data['total'];

// Build base URL for pagination
$base_url = "index.php?search=" . urlencode($search) . "&filter=" . urlencode($filter) . "&";
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Nasabah</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .hero {
            background: linear-gradient(135deg, #6f42c1, #432874);
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
                        <a class="nav-link active"
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
                Kelola Nasabah
            </h1>

            <p class="mb-0 opacity-75">
                Tambah, edit, dan hapus data nasabah Bank Multimedia.
            </p>

        </div>
    </header>

    <!-- Workspace -->
    <main class="flex-grow-1">
        <div class="container">

            <!-- Notifikasi -->
            <?php if (isset($_GET['pesan'])): ?>
                <?php
                $pesan = $_GET['pesan'];
                $alert_class = 'alert-info';
                $alert_msg = '';
                switch ($pesan) {
                    case 'verifikasi_berhasil':
                        $alert_class = 'alert-success';
                        $alert_msg = '<strong>Berhasil!</strong> Akun nasabah telah diverifikasi dan diaktifkan.';
                        break;
                    case 'aktivasi_berhasil':
                        $alert_class = 'alert-success';
                        $alert_msg = '<strong>Berhasil!</strong> Akun nasabah berhasil diaktifkan.';
                        break;
                    case 'nonaktif_berhasil':
                        $alert_class = 'alert-warning';
                        $alert_msg = '<strong>Berhasil!</strong> Akun nasabah berhasil dinonaktifkan.';
                        break;
                    case 'reset_berhasil':
                        $temp_pass = isset($_GET['temp_pass']) ? htmlspecialchars($_GET['temp_pass']) : '';
                        $uid = isset($_GET['uid']) ? (int) $_GET['uid'] : 0;
                        $alert_class = 'alert-success';
                        $alert_msg = '<strong>Berhasil!</strong> Password nasabah (ID: ' . $uid . ') telah direset.<br><strong>Password Sementara: <code>' . $temp_pass . '</code></strong><br><small class="text-danger">Catat password ini! Hanya ditampilkan sekali.</small>';
                        break;
                    default:
                        if (strpos($pesan, 'gagal') !== false) {
                            $alert_class = 'alert-danger';
                            $alert_msg = '<strong>Gagal!</strong> Operasi tidak berhasil. Silakan coba lagi.';
                        }
                        break;
                }
                if ($alert_msg):
                ?>
                    <div class="alert <?= $alert_class; ?> alert-dismissible fade show" role="alert">
                        <?= $alert_msg; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($detail): ?>
                <!-- Detail Nasabah -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-semibold"><i class="fas fa-user me-2 text-primary"></i>Detail Nasabah</h6>
                        <a href="index.php?search=<?= urlencode($search); ?>&filter=<?= urlencode($filter); ?>&page=<?= $page; ?>" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Kembali
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 text-center mb-3">
                                <?php
                                $foto = $detail['foto_profil'] ?? 'default.png';
                                $foto_path = "../../assets/uploads/" . $foto;
                                ?>
                                <img src="<?= htmlspecialchars($foto_path); ?>" alt="Foto Profil" class="rounded-circle mb-2" style="width: 120px; height: 120px; object-fit: cover; border: 3px solid #dee2e6;" onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($detail['nama_lengkap']); ?>&size=120&background=6f42c1&color=fff'">
                                <h6 class="fw-semibold mb-0"><?= htmlspecialchars($detail['nama_lengkap']); ?></h6>
                                <small class="text-muted"><?= htmlspecialchars($detail['email']); ?></small>
                                <div class="mt-2">
                                    <?php
                                    $status = $detail['status_akun'];
                                    $badge = 'bg-secondary';
                                    if ($status === 'Aktif') $badge = 'bg-success';
                                    elseif ($status === 'Pending') $badge = 'bg-warning text-dark';
                                    elseif ($status === 'Nonaktif') $badge = 'bg-danger';
                                    ?>
                                    <span class="badge <?= $badge; ?>"><?= htmlspecialchars($status); ?></span>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <table class="table table-borderless table-sm">
                                    <tr><td class="text-muted fw-semibold">ID</td><td><?= $detail['id']; ?></td></tr>
                                    <tr><td class="text-muted fw-semibold">Nama Lengkap</td><td><?= htmlspecialchars($detail['nama_lengkap']); ?></td></tr>
                                    <tr><td class="text-muted fw-semibold">Email</td><td><?= htmlspecialchars($detail['email']); ?></td></tr>
                                    <tr><td class="text-muted fw-semibold">No. HP</td><td><?= htmlspecialchars($detail['no_hp'] ?? '-'); ?></td></tr>
                                    <tr><td class="text-muted fw-semibold">Tanggal Registrasi</td><td><?= date('d M Y H:i', strtotime($detail['created_at'])); ?></td></tr>
                                    <tr><td class="text-muted fw-semibold">Jumlah Rekening</td><td><?= $detail['jumlah_rekening']; ?></td></tr>
                                    <tr><td class="text-muted fw-semibold">Total Saldo</td><td class="fw-bold text-success"><?= formatCurrency($detail['total_saldo']); ?></td></tr>
                                </table>
                            </div>
                            <div class="col-md-4">
                                <h6 class="fw-semibold mb-3">Aksi Admin</h6>
                                <div class="d-grid gap-2">
                                    <?php if ($detail['status_akun'] === 'Pending'): ?>
                                        <form method="POST" action="../controllers/NasabahController.php" onsubmit="return confirm('Verifikasi akun nasabah ini?')">
                                            <input type="hidden" name="action" value="verifikasi">
                                            <input type="hidden" name="user_id" value="<?= $detail['id']; ?>">
                                            <button class="btn btn-success btn-sm w-100"><i class="fas fa-check me-1"></i>Verifikasi Akun</button>
                                        </form>
                                    <?php endif; ?>
                                    <?php if ($detail['status_akun'] === 'Nonaktif' || $detail['status_akun'] === 'Pending'): ?>
                                        <form method="POST" action="../controllers/NasabahController.php" onsubmit="return confirm('Aktifkan akun nasabah ini?')">
                                            <input type="hidden" name="action" value="aktivasi">
                                            <input type="hidden" name="user_id" value="<?= $detail['id']; ?>">
                                            <button class="btn btn-primary btn-sm w-100"><i class="fas fa-toggle-on me-1"></i>Aktifkan Akun</button>
                                        </form>
                                    <?php endif; ?>
                                    <?php if ($detail['status_akun'] === 'Aktif'): ?>
                                        <form method="POST" action="../controllers/NasabahController.php" onsubmit="return confirm('Nonaktifkan akun nasabah ini? Data tidak akan dihapus.')">
                                            <input type="hidden" name="action" value="nonaktifkan">
                                            <input type="hidden" name="user_id" value="<?= $detail['id']; ?>">
                                            <button class="btn btn-outline-danger btn-sm w-100"><i class="fas fa-toggle-off me-1"></i>Nonaktifkan Akun</button>
                                        </form>
                                    <?php endif; ?>
                                    <form method="POST" action="../controllers/NasabahController.php" onsubmit="return confirm('Reset password nasabah ini? Password baru akan ditampilkan sekali.')">
                                        <input type="hidden" name="action" value="reset_password">
                                        <input type="hidden" name="user_id" value="<?= $detail['id']; ?>">
                                        <button class="btn btn-warning btn-sm w-100"><i class="fas fa-key me-1"></i>Reset Password</button>
                                    </form>
                                </div>

                                <?php if (!empty($detail_rekening)): ?>
                                    <h6 class="fw-semibold mt-4 mb-2">Daftar Rekening</h6>
                                    <?php foreach ($detail_rekening as $rek): ?>
                                        <div class="border rounded p-2 mb-2 small">
                                            <div class="fw-semibold"><?= htmlspecialchars($rek['jenis_rekening']); ?></div>
                                            <div>No: <?= htmlspecialchars(decrypt($rek['nomor_rekening_encrypted'])); ?></div>
                                            <div>Saldo: <strong><?= formatCurrency($rek['saldo']); ?></strong></div>
                                            <div>Status: <span class="badge <?= $rek['status_rekening'] === 'Aktif' ? 'bg-success' : 'bg-danger'; ?> badge-sm"><?= htmlspecialchars($rek['status_rekening']); ?></span></div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
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
                            <input type="text" name="search" class="form-control" placeholder="Cari nama, email, atau no. HP..." value="<?= htmlspecialchars($search); ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Filter Status</label>
                        <select name="filter" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="Pending" <?= $filter === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="Aktif" <?= $filter === 'Aktif' ? 'selected' : ''; ?>>Aktif</option>
                            <option value="Nonaktif" <?= $filter === 'Nonaktif' ? 'selected' : ''; ?>>Nonaktif</option>
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

            <!-- Tabel Nasabah -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-semibold"><i class="fas fa-users me-2 text-primary"></i>Daftar Nasabah <span class="badge bg-primary ms-1"><?= $total_rows; ?></span></h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Foto</th>
                                    <th>Nama Lengkap</th>
                                    <th>Email</th>
                                    <th>No. HP</th>
                                    <th>Status</th>
                                    <th>Rekening</th>
                                    <th>Total Saldo</th>
                                    <th>Tgl Registrasi</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($nasabah_list)): ?>
                                    <?php
                                    $no = ($page - 1) * $per_page + 1;
                                    foreach ($nasabah_list as $n):
                                        $status = $n['status_akun'];
                                        $badge = 'bg-secondary';
                                        if ($status === 'Aktif') $badge = 'bg-success';
                                        elseif ($status === 'Pending') $badge = 'bg-warning text-dark';
                                        elseif ($status === 'Nonaktif') $badge = 'bg-danger';
                                        $foto = $n['foto_profil'] ?? 'default.png';
                                    ?>
                                        <tr>
                                            <td><?= $no++; ?></td>
                                            <td>
                                                <img src="../../assets/uploads/<?= htmlspecialchars($foto); ?>" alt="Foto" class="rounded-circle" style="width:36px;height:36px;object-fit:cover;" onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($n['nama_lengkap']); ?>&size=36&background=6f42c1&color=fff'">
                                            </td>
                                            <td class="fw-semibold"><?= htmlspecialchars($n['nama_lengkap']); ?></td>
                                            <td><small><code><?= htmlspecialchars($n['email']); ?></code></small></td>
                                            <td><small><?= htmlspecialchars($n['no_hp'] ?? '-'); ?></small></td>
                                            <td><span class="badge <?= $badge; ?>"><?= htmlspecialchars($status); ?></span></td>
                                            <td class="text-center"><?= $n['jumlah_rekening']; ?></td>
                                            <td class="fw-semibold"><?= formatCurrency($n['total_saldo']); ?></td>
                                            <td><small class="text-muted"><?= date('d-m-Y', strtotime($n['created_at'])); ?></small></td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="index.php?detail=<?= $n['id']; ?>&search=<?= urlencode($search); ?>&filter=<?= urlencode($filter); ?>&page=<?= $page; ?>" class="btn btn-outline-primary" title="Detail">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <?php if ($status === 'Pending'): ?>
                                                        <form method="POST" action="../controllers/NasabahController.php" class="d-inline" onsubmit="return confirm('Verifikasi akun ini?')">
                                                            <input type="hidden" name="action" value="verifikasi">
                                                            <input type="hidden" name="user_id" value="<?= $n['id']; ?>">
                                                            <button class="btn btn-outline-success" title="Verifikasi"><i class="fas fa-check"></i></button>
                                                        </form>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="10" class="text-center py-4 text-muted">Tidak ada data nasabah ditemukan.</td>
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