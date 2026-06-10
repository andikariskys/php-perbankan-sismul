<?php
/**
 * Nasabah Controller
 * Menangani aksi admin: verifikasi, aktivasi, nonaktifkan, reset password.
 */
session_start();
include '../../config/database.php';
include '../helpers/admin_auth.php';
include '../models/NasabahModel.php';

adminGuard();

$action = isset($_POST['action']) ? $_POST['action'] : '';
$admin_id = $_SESSION['user_id'];

switch ($action) {
    case 'verifikasi':
        $user_id = isset($_POST['user_id']) ? (int) $_POST['user_id'] : 0;
        if ($user_id > 0) {
            $result = verifikasiAkun($conn, $user_id, $admin_id);
            if ($result) {
                catatAuditAdmin($conn, $admin_id, $user_id, 'Verifikasi Akun', "Admin memverifikasi akun nasabah ID: $user_id");
                header('Location: ../nasabah/index.php?pesan=verifikasi_berhasil');
            } else {
                header('Location: ../nasabah/index.php?pesan=verifikasi_gagal');
            }
        }
        break;

    case 'aktivasi':
        $user_id = isset($_POST['user_id']) ? (int) $_POST['user_id'] : 0;
        if ($user_id > 0) {
            $result = updateStatusAkun($conn, $user_id, $admin_id, 'Aktif');
            if ($result) {
                catatAuditAdmin($conn, $admin_id, $user_id, 'Aktivasi Akun', "Admin mengaktifkan akun nasabah ID: $user_id");
                header('Location: ../nasabah/index.php?pesan=aktivasi_berhasil');
            } else {
                header('Location: ../nasabah/index.php?pesan=aktivasi_gagal');
            }
        }
        break;

    case 'nonaktifkan':
        $user_id = isset($_POST['user_id']) ? (int) $_POST['user_id'] : 0;
        if ($user_id > 0) {
            $result = updateStatusAkun($conn, $user_id, $admin_id, 'Nonaktif');
            if ($result) {
                catatAuditAdmin($conn, $admin_id, $user_id, 'Nonaktifkan Akun', "Admin menonaktifkan akun nasabah ID: $user_id");
                header('Location: ../nasabah/index.php?pesan=nonaktif_berhasil');
            } else {
                header('Location: ../nasabah/index.php?pesan=nonaktif_gagal');
            }
        }
        break;

    case 'reset_password':
        $user_id = isset($_POST['user_id']) ? (int) $_POST['user_id'] : 0;
        if ($user_id > 0) {
            $temp_password = resetPasswordNasabah($conn, $user_id, $admin_id);
            if ($temp_password) {
                catatAuditAdmin($conn, $admin_id, $user_id, 'Reset Password', "Admin mereset password nasabah ID: $user_id");
                header('Location: ../nasabah/index.php?pesan=reset_berhasil&temp_pass=' . urlencode($temp_password) . '&uid=' . $user_id);
            } else {
                header('Location: ../nasabah/index.php?pesan=reset_gagal');
            }
        }
        break;

    default:
        header('Location: ../nasabah/index.php');
        break;
}
exit;
