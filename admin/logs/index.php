<?php
session_start();
include "../../config/database.php";

include "../../helper/format.php";
include "../helpers/admin_auth.php";
include "../helpers/pagination.php";

adminGuard();

$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'semua';
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

$where_clauses = [];
$params = [];
$types = '';

// Filter pencarian
if (!empty($search_query)) {
    $search_like = "%{$search_query}%";
    $where_clauses[] = "(u.nama_lengkap LIKE ? OR u.email LIKE ? OR a.aktivitas LIKE ? OR a.deskripsi LIKE ?)";
    $params[] = $search_like;
    $params[] = $search_like;
    $params[] = $search_like;
    $params[] = $search_like;
    $types .= 'ssss';
}

// Filter jenis aktivitas tab
switch ($active_tab) {
    case 'login':
        $where_clauses[] = "a.aktivitas IN ('Login', 'Logout')";
        break;
    case 'password':
        $where_clauses[] = "(a.aktivitas = 'Ubah Password' OR a.aktivitas LIKE '%Reset Password%')";
        break;
    case 'setor':
        $where_clauses[] = "(a.aktivitas LIKE '%Setor%' OR a.aktivitas = 'SETOR')";
        break;
    case 'tarik':
        $where_clauses[] = "(a.aktivitas LIKE '%Tarik%' OR a.aktivitas = 'TARIK')";
        break;
    case 'transfer':
        $where_clauses[] = "(a.aktivitas LIKE '%Transfer%' OR a.aktivitas LIKE 'TRANSFER%')";
        break;
    case 'topup':
        $where_clauses[] = "(a.aktivitas LIKE '%Top Up%' OR a.aktivitas LIKE '%Topup%' OR a.aktivitas LIKE 'TOPUP%')";
        break;
    case 'admin':
        $where_clauses[] = "(a.aktivitas LIKE '%Verifikasi%' OR a.aktivitas LIKE '%Aktivasi%' OR a.aktivitas LIKE '%Nonaktif%' OR a.aktivitas LIKE '%Reset Password%' OR a.aktivitas LIKE '%Lihat%')";
        break;
}

$where_sql = "";
if (count($where_clauses) > 0) {
    $where_sql = "WHERE " . implode(" AND ", $where_clauses);
}

// Count total rows
$count_sql = "SELECT COUNT(*) AS total FROM audit_log a JOIN users u ON a.user_id = u.id $where_sql";
$stmt = mysqli_prepare($conn, $count_sql);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$total_rows = (int) mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['total'];
mysqli_stmt_close($stmt);

// Fetch logs with pagination
$query_logs = "SELECT a.*, u.nama_lengkap, u.email, r.nama_role
               FROM audit_log a
               JOIN users u ON a.user_id = u.id
               JOIN roles r ON u.role_id = r.id
               $where_sql
               ORDER BY a.created_at DESC
               LIMIT ? OFFSET ?";
$params[] = $per_page;
$params[] = $offset;
$types .= 'ii';

$stmt = mysqli_prepare($conn, $query_logs);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$logs_result = mysqli_stmt_get_result($stmt);

$base_url = "index.php?tab=" . urlencode($active_tab) . "&search=" . urlencode($search_query) . "&";


?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Logs</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .hero {
            background: linear-gradient(135deg, #198754, #0f5132);
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
                        <a class="nav-link"
                            href="../aktivitas/index.php">
                            Aktivitas
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link active"
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
                Audit Logs
            </h1>

            <p class="mb-0 opacity-75">
                Riwayat log sistem untuk keperluan audit dan keamanan.
            </p>

        </div>
    </header>

    <!-- Workspace -->
    <main class="flex-grow-1">
        <div class="container-fluid py-4">


            <!-- Filter & Search Bar -->
            <div class="card p-3 mb-4">
                <form method="GET" action="index.php" class="row g-3 align-items-center">
                    <input type="hidden" name="tab" id="active-tab-input" value="<?= htmlspecialchars($active_tab); ?>">
                    <div class="col-md-9">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" name="search" class="form-control border-start-0 bg-light" placeholder="Cari berdasarkan nama, email, aktivitas, atau deskripsi..." value="<?= htmlspecialchars($search_query); ?>">
                        </div>
                    </div>
                    <div class="col-md-3 d-grid">
                        <button type="submit" class="btn btn-primary fw-semibold">
                            Cari Log
                        </button>
                    </div>
                </form>
            </div>

            <!-- Tab & Table Card -->
            <div class="card p-4">
                
                <!-- Tab Menu -->
                <ul class="nav nav-tabs mb-4" id="logTab">
                    <li class="nav-item">
                        <a class="nav-link <?= $active_tab === 'semua' ? 'active' : '' ?>" href="index.php?tab=semua&search=<?= urlencode($search_query) ?>">
                            Semua
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $active_tab === 'login' ? 'active' : '' ?>" href="index.php?tab=login&search=<?= urlencode($search_query) ?>">
                            Login & Logout
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $active_tab === 'password' ? 'active' : '' ?>" href="index.php?tab=password&search=<?= urlencode($search_query) ?>">
                            Ubah Password
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $active_tab === 'setor' ? 'active' : '' ?>" href="index.php?tab=setor&search=<?= urlencode($search_query) ?>">
                            Setor Dana
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $active_tab === 'tarik' ? 'active' : '' ?>" href="index.php?tab=tarik&search=<?= urlencode($search_query) ?>">
                            Tarik Dana
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $active_tab === 'transfer' ? 'active' : '' ?>" href="index.php?tab=transfer&search=<?= urlencode($search_query) ?>">
                            Transfer
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $active_tab === 'topup' ? 'active' : '' ?>" href="index.php?tab=topup&search=<?= urlencode($search_query) ?>">
                            Top Up E-Wallet
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $active_tab === 'admin' ? 'active' : '' ?>" href="index.php?tab=admin&search=<?= urlencode($search_query) ?>">
                            Admin
                        </a>
                    </li>
                </ul>

                <!-- Table Content -->
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
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
                            <?php if (mysqli_num_rows($logs_result) > 0): ?>
                                <?php 
                                $no = ($page - 1) * $per_page + 1;
                                while ($row = mysqli_fetch_assoc($logs_result)): 
                                    $badge_class = 'bg-secondary';
                                    $act = $row['aktivitas'];
                                    
                                    if ($act === 'Login' || $act === 'Logout') {
                                        $badge_class = 'bg-info text-dark';
                                    } elseif ($act === 'Ubah Password' || strpos($act, 'Reset Password') !== false) {
                                        $badge_class = 'bg-warning text-dark';
                                    } elseif (strpos($act, 'Setor') !== false || $act === 'SETOR') {
                                        $badge_class = 'bg-success';
                                    } elseif (strpos($act, 'Tarik') !== false || $act === 'TARIK') {
                                        $badge_class = 'bg-danger';
                                    } elseif (strpos($act, 'Transfer') !== false || strpos($act, 'TRANSFER') !== false) {
                                        $badge_class = 'bg-primary';
                                    } elseif (strpos($act, 'Top Up') !== false || strpos($act, 'Topup') !== false || strpos($act, 'TOPUP') !== false) {
                                        $badge_class = 'bg-dark';
                                    } elseif (strpos($act, 'Verifikasi') !== false || strpos($act, 'Aktivasi') !== false) {
                                        $badge_class = 'bg-success';
                                    } elseif (strpos($act, 'Nonaktif') !== false) {
                                        $badge_class = 'bg-danger';
                                    } elseif (strpos($act, 'Lihat') !== false) {
                                        $badge_class = 'bg-secondary';
                                    }
                                ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><small class="text-muted"><?= date('d-m-y H:i:s', strtotime($row['created_at'])); ?></small></td>
                                        <td>
                                            <div class="fw-semibold text-dark"><?= htmlspecialchars($row['nama_lengkap']); ?></div>
                                            <small class="text-muted"><code><?= htmlspecialchars($row['email']); ?></code></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border"><?= htmlspecialchars($row['nama_role']); ?></span>
                                        </td>
                                        <td>
                                            <span class="badge <?= $badge_class; ?> px-2 py-1"><?= htmlspecialchars($act); ?></span>
                                        </td>
                                        <td><span class="text-muted small"><?= htmlspecialchars($row['deskripsi']); ?></span></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        Tidak ada audit log yang cocok dengan filter aktif.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($total_rows > $per_page): ?>
                    <div class="mt-3">
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