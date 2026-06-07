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
$password_lama = $_POST['password_lama'];
$password_baru = $_POST['password_baru'];
$konfirmasi_password = $_POST['konfirmasi_password'];

if (empty($password_lama) || empty($password_baru) || empty($konfirmasi_password)) {
    header('Location: index.php?pesan=field_kosong');
    exit;
}

if (strlen($password_baru) < 8) {
    header('Location: index.php?pesan=password_pendek');
    exit;
}

if ($password_baru !== $konfirmasi_password) {
    header('Location: index.php?pesan=password_tidak_cocok');
    exit;
}

$stmt = mysqli_prepare($conn, "SELECT password FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if (!password_verify($password_lama, $user['password'])) {
    header('Location: index.php?pesan=password_lama_salah');
    exit;
}

$hash = password_hash($password_baru, PASSWORD_DEFAULT);

$stmt = mysqli_prepare($conn, "UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
mysqli_stmt_bind_param($stmt, "si", $hash, $user_id);

if (mysqli_stmt_execute($stmt)) {
    header('Location: index.php?pesan=password_berhasil');
} else {
    header('Location: index.php?pesan=password_gagal');
}
exit;
