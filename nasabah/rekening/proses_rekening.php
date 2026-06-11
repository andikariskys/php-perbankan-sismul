<?php
session_start();
include "../../config/database.php";
include_once "../../helper/encryption.php";

if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php?pesan=belum_login');
    exit;
}

if ($_SESSION['nama_role'] !== 'Nasabah') {
    header('Location: /login.php?pesan=akses_ditolak');
    exit;
}

/**
 * Generate unique account number
 * Format: 026 (bank code) + 10 random digits
 */
function generateNomorRekening()
{
    // Generate 10-digit random number using cryptographically secure random_int
    return '026' . random_int(1000000000, 9999999999);
}

if (isset($_POST['request_rekening'])) {
    $user_id = $_SESSION['user_id'];
    $jenis_rekening = htmlspecialchars(trim($_POST['jenis_rekening']));

    // Cek jumlah rekening yang sudah ada (Limit Maksimal 2)
    $check_query = "SELECT COUNT(*) as total FROM rekening WHERE user_id = ?";
    $stmt_check = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($stmt_check, "i", $user_id);
    mysqli_stmt_execute($stmt_check);
    $result_check = mysqli_stmt_get_result($stmt_check);
    $data_check = mysqli_fetch_assoc($result_check);
    mysqli_stmt_close($stmt_check);

    if ($data_check['total'] >= 2) {
        header('Location: index.php?pesan=limit_tercapai');
        exit;
    }

    // Validasi jenis_rekening agar sesuai dengan ENUM database ('Tabungan', 'Giro')
    if ($jenis_rekening !== 'Tabungan' && $jenis_rekening !== 'Giro') {
        $jenis_rekening = 'Tabungan';
    }

    // Generate dan enkripsi nomor rekening baru
    $nomor_rekening = generateNomorRekening();
    $nomor_rekening_encrypted = encrypt($nomor_rekening);

    // Simpan ke database menggunakan Prepared Statement
    $query = "INSERT INTO rekening (user_id, nomor_rekening_encrypted, jenis_rekening, saldo, status_rekening) VALUES (?, ?, ?, 0.00, 'Aktif')";

    if ($stmt = mysqli_prepare($conn, $query)) {
        mysqli_stmt_bind_param($stmt, "iss", $user_id, $nomor_rekening_encrypted, $jenis_rekening);
        $exec = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        if ($exec) {
            header('Location: index.php?pesan=sukses_buat');
            exit;
        }
    }

    header('Location: index.php?pesan=gagal_buat');
    exit;
} else {
    // Jika diakses tanpa submit POST, redirect ke index.php
    header('Location: index.php');
    exit;
}