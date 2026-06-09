<?php
session_start();
include "../../config/database.php";

if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php?pesan=belum_login');
    exit;
}

if ($_SESSION['nama_role'] !== 'Admin') {
    header('Location: /login.php?pesan=akses_ditolak');
    exit;
}

$type = isset($_GET['type']) ? $_GET['type'] : 'semua';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$where_clauses = [];

if (!empty($search)) {
    $search_escaped = mysqli_real_escape_string($conn, $search);
    $where_clauses[] = "(u.nama_lengkap LIKE '%$search_escaped%' OR u.email LIKE '%$search_escaped%' OR a.aktivitas LIKE '%$search_escaped%' OR a.deskripsi LIKE '%$search_escaped%')";
}

switch ($type) {
    case 'login':
        $where_clauses[] = "a.aktivitas IN ('Login', 'Logout')";
        break;
    case 'password':
        $where_clauses[] = "a.aktivitas = 'Ubah Password'";
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
}

$where_sql = "";
if (count($where_clauses) > 0) {
    $where_sql = "WHERE " . implode(" AND ", $where_clauses);
}

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

$result = mysqli_query($conn, $query);
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

fclose($output);
exit;
