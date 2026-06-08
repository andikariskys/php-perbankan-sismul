<?php

if (!function_exists('catatAuditLog')) {
    /**
     * Mencatat aktivitas penting pengguna ke tabel audit_log.
     * 
     * @param mysqli $conn Koneksi database
     * @param int $user_id ID Pengguna
     * @param string $aktivitas Nama aktivitas (misal: 'Login', 'Transfer', dsb.)
     * @param string $deskripsi Detail penjelasan aktivitas
     * @return bool True jika berhasil, false jika gagal
     */
    function catatAuditLog($conn, $user_id, $aktivitas, $deskripsi) {
        $query = "INSERT INTO audit_log (user_id, aktivitas, deskripsi) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "iss", $user_id, $aktivitas, $deskripsi);
            $result = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            return $result;
        }
        return false;
    }
}

if (!function_exists('tambahNotifikasi')) {
    /**
     * Menambahkan pesan notifikasi baru ke database untuk pengguna tertentu.
     * 
     * @param mysqli $conn Koneksi database
     * @param int $user_id ID Pengguna penerima notifikasi
     * @param string $judul Judul notifikasi
     * @param string $pesan Detail pesan notifikasi
     * @return bool True jika berhasil, false jika gagal
     */
    function tambahNotifikasi($conn, $user_id, $judul, $pesan) {
        $query = "INSERT INTO notifikasi (user_id, judul, pesan) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "iss", $user_id, $judul, $pesan);
            $result = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            return $result;
        }
        return false;
    }
}
