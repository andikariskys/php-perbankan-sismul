<?php
session_start();
include "../../config/database.php";
include "../helpers/admin_auth.php";

adminGuard();

$type = isset($_GET['type']) ? $_GET['type'] : 'semua';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$where_clauses = [];
$params = [];
$types = '';

if (!empty($search)) {
    $search_like = "%{$search}%";
    $where_clauses[] = "(u.nama_lengkap LIKE ? OR u.email LIKE ? OR a.aktivitas LIKE ? OR a.deskripsi LIKE ?)";
    $params[] = $search_like;
    $params[] = $search_like;
    $params[] = $search_like;
    $params[] = $search_like;
    $types .= 'ssss';
}

switch ($type) {
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

// Catat audit: admin mengekspor log
catatAuditAdmin($conn, $_SESSION['user_id'], $_SESSION['user_id'], 'Ekspor Log', "Admin mengekspor audit log dengan filter: $type");

// Set headers for download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=admin_audit_' . $type . '_' . date('Ymd_His') . '.csv');

$output = fopen('php://output', 'w');

fputcsv($output, ['No', 'Waktu', 'Nama Pengguna', 'Email', 'Role', 'Aktivitas', 'Deskripsi']);

$query = "SELECT a.*, u.nama_lengkap, u.email, r.nama_role
          FROM audit_log a
          JOIN users u ON a.user_id = u.id
          JOIN roles r ON u.role_id = r.id
          $where_sql
          ORDER BY a.created_at DESC";

$stmt = mysqli_prepare($conn, $query);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$no = 1;
while ($row = mysqli_fetch_assoc($result)) {
    fputcsv($output, [
        $no++,
        $row['created_at'],
        $row['nama_lengkap'],
        $row['email'],
        $row['nama_role'],
        $row['aktivitas'],
        $row['deskripsi']
    ]);
}
mysqli_stmt_close($stmt);

fclose($output);
exit;
