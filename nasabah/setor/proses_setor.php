<?php
session_start();
include "../../config/database.php";
include "../../helper/encryption.php";
include "../../helper/format.php";

// Cek login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php?pesan=belum_login');
    exit;
}

// Cek role
if ($_SESSION['nama_role'] !== 'Nasabah') {
    header('Location: ../../login.php?pesan=akses_ditolak');
    exit;
}

// Cek method POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Validasi input
$rekening_id = filter_input(INPUT_POST, 'rekening_id', FILTER_VALIDATE_INT);
$merchant_id = filter_input(INPUT_POST, 'merchant_id', FILTER_VALIDATE_INT);
$nominal = filter_input(INPUT_POST, 'nominal', FILTER_VALIDATE_FLOAT);

if (!$rekening_id || !$merchant_id || !$nominal || $nominal < 10000) {
    header('Location: index.php?pesan=input_tidak_valid');
    exit;
}

// Verifikasi kepemilikan rekening
$query_cek = "SELECT id, saldo FROM rekening 
              WHERE id = ? AND user_id = ? AND status_rekening = 'Aktif'";
$stmt_cek = mysqli_prepare($conn, $query_cek);
mysqli_stmt_bind_param($stmt_cek, "ii", $rekening_id, $user_id);
mysqli_stmt_execute($stmt_cek);
$result_cek = mysqli_stmt_get_result($stmt_cek);

if (mysqli_num_rows($result_cek) === 0) {
    header('Location: index.php?pesan=rekening_tidak_valid');
    exit;
}

$rekening = mysqli_fetch_assoc($result_cek);

// Verifikasi merchant
$query_merchant = "SELECT id, nama_merchant FROM merchant WHERE id = ? AND status_merchant = 'Aktif'";
$stmt_merchant = mysqli_prepare($conn, $query_merchant);
mysqli_stmt_bind_param($stmt_merchant, "i", $merchant_id);
mysqli_stmt_execute($stmt_merchant);
$result_merchant = mysqli_stmt_get_result($stmt_merchant);

if (mysqli_num_rows($result_merchant) === 0) {
    header('Location: index.php?pesan=merchant_tidak_valid');
    exit;
}

$merchant = mysqli_fetch_assoc($result_merchant);

// Hitung saldo
$saldo_sebelum = $rekening['saldo'];
$saldo_sesudah = $saldo_sebelum + $nominal;
$keterangan = "Setor melalui " . $merchant['nama_merchant'];

// Mulai database transaction
mysqli_begin_transaction($conn);

try {
    // 1. Update saldo rekening
    $query_update = "UPDATE rekening SET saldo = ? WHERE id = ?";
    $stmt_update = mysqli_prepare($conn, $query_update);
    mysqli_stmt_bind_param($stmt_update, "di", $saldo_sesudah, $rekening_id);
    mysqli_stmt_execute($stmt_update);

    // 2. Insert ke tabel transaksi
    $query_transaksi = "INSERT INTO transaksi 
                        (rekening_id, jenis_transaksi, nominal, saldo_sebelum, saldo_sesudah, keterangan) 
                        VALUES (?, 'SETOR', ?, ?, ?, ?)";
    $stmt_transaksi = mysqli_prepare($conn, $query_transaksi);
    mysqli_stmt_bind_param($stmt_transaksi, "iddds", $rekening_id, $nominal, $saldo_sebelum, $saldo_sesudah, $keterangan);
    mysqli_stmt_execute($stmt_transaksi);

    // Ambil ID transaksi yang baru dibuat
    $transaksi_id = mysqli_insert_id($conn);

    // 3. Insert audit log
    $aktivitas = "Setor Dana";
    $deskripsi = "Nasabah melakukan setor dana sebesar " . formatCurrency($nominal) . " melalui " . $merchant['nama_merchant'];
    $query_audit = "INSERT INTO audit_log (user_id, aktivitas, deskripsi) VALUES (?, ?, ?)";
    $stmt_audit = mysqli_prepare($conn, $query_audit);
    mysqli_stmt_bind_param($stmt_audit, "iss", $user_id, $aktivitas, $deskripsi);
    mysqli_stmt_execute($stmt_audit);

    // Commit transaction
    mysqli_commit($conn);

    // Redirect dengan pesan sukses + ID transaksi untuk cetak resi
    header("Location: index.php?pesan=berhasil&transaksi_id=" . $transaksi_id);
    exit;

} catch (Exception $e) {
    // Rollback jika gagal
    mysqli_rollback($conn);
    header("Location: index.php?pesan=gagal");
    exit;
}
