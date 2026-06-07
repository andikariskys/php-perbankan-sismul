<?php
session_start();
include "../../config/database.php";

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php?pesan=belum_login');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$user_id = $_SESSION['user_id'];

$nama_lengkap = htmlspecialchars(trim($_POST['nama_lengkap']));
$no_hp = htmlspecialchars(trim($_POST['no_hp']));

if (empty($nama_lengkap) || empty($no_hp)) {
    header('Location: index.php?pesan=field_kosong');
    exit;
}

$stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE no_hp = ? AND id != ?");
mysqli_stmt_bind_param($stmt, "si", $no_hp, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {
    header('Location: index.php?pesan=no_hp_ada');
    exit;
}

$stmt = mysqli_prepare($conn, "UPDATE users SET nama_lengkap = ?, no_hp = ?, updated_at = NOW() WHERE id = ?");
mysqli_stmt_bind_param($stmt, "ssi", $nama_lengkap, $no_hp, $user_id);

if (mysqli_stmt_execute($stmt)) {
    
    $_SESSION['nama_lengkap'] = $nama_lengkap;
    header('Location: index.php?pesan=profil_berhasil');
} else {
    header('Location: index.php?pesan=profil_gagal');
}
exit;
