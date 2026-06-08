<?php
session_start();
include "../../config/database.php";
include "../../helper/encryption.php";

if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php?pesan=belum_login');
    exit;
}

if ($_SESSION['nama_role'] !== 'Nasabah') {
    header('Location: /login.php?pesan=akses_ditolak');
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$type = isset($_GET['type']) ? $_GET['type'] : 'transaksi';

// Set headers for download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=riwayat_' . $type . '_' . date('Ymd_His') . '.csv');

$output = fopen('php://output', 'w');

if ($type === 'transaksi') {
    // Header CSV
    fputcsv($output, ['No', 'Tanggal', 'No Rekening', 'Jenis Rekening', 'Aktivitas Transaksi', 'Nominal', 'Saldo Sebelum', 'Saldo Sesudah', 'Keterangan']);
    
    $query = "SELECT t.*, r.nomor_rekening_encrypted, r.jenis_rekening
              FROM transaksi t
              JOIN rekening r ON t.rekening_id = r.id
              WHERE r.user_id = ?
              ORDER BY t.created_at DESC";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $no = 1;
    while ($row = mysqli_fetch_assoc($result)) {
        $no_rek = decrypt($row['nomor_rekening_encrypted']);
        fputcsv($output, [
            $no++,
            $row['created_at'],
            $no_rek,
            $row['jenis_rekening'],
            $row['jenis_transaksi'],
            $row['nominal'],
            $row['saldo_sebelum'],
            $row['saldo_sesudah'],
            $row['keterangan']
        ]);
    }
    mysqli_stmt_close($stmt);

} elseif ($type === 'audit') {
    // Header CSV
    fputcsv($output, ['No', 'Waktu', 'Aktivitas', 'Deskripsi']);
    
    $query = "SELECT * FROM audit_log WHERE user_id = ? ORDER BY created_at DESC";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $no = 1;
    while ($row = mysqli_fetch_assoc($result)) {
        fputcsv($output, [
            $no++,
            $row['created_at'],
            $row['aktivitas'],
            $row['deskripsi']
        ]);
    }
    mysqli_stmt_close($stmt);

} elseif ($type === 'notifikasi') {
    // Header CSV
    fputcsv($output, ['No', 'Tanggal', 'Judul', 'Pesan', 'Status']);
    
    $query = "SELECT * FROM notifikasi WHERE user_id = ? ORDER BY created_at DESC";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $no = 1;
    while ($row = mysqli_fetch_assoc($result)) {
        fputcsv($output, [
            $no++,
            $row['created_at'],
            $row['judul'],
            $row['pesan'],
            $row['status_baca']
        ]);
    }
    mysqli_stmt_close($stmt);
}

fclose($output);
exit;
