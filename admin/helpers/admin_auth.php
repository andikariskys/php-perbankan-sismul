<?php
/**
 * Admin Auth Helper / Middleware
 * Pastikan dipanggil sebelum output HTML apapun.
 */

if (!function_exists('adminGuard')) {
    /**
     * Middleware: cek session dan role admin.
     * Redirect ke login jika belum login atau bukan admin.
     */
    function adminGuard() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login.php?pesan=belum_login');
            exit;
        }
        if ($_SESSION['nama_role'] !== 'Admin') {
            header('Location: /login.php?pesan=akses_ditolak');
            exit;
        }

        // Regenerate session ID secara berkala untuk keamanan
        if (!isset($_SESSION['last_regeneration'])) {
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        } elseif (time() - $_SESSION['last_regeneration'] > 300) {
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        }
    }
}

if (!function_exists('catatAuditAdmin')) {
    /**
     * Catat aktivitas admin ke audit_log dengan informasi tambahan (ip, user_agent).
     * Kolom admin_id dan ip_address / user_agent ditambahkan via migration SQL baru.
     *
     * @param mysqli $conn       Koneksi database
     * @param int    $admin_id   ID admin yang melakukan aksi
     * @param int    $target_user_id ID user target (bisa sama dengan admin_id)
     * @param string $aktivitas  Nama aktivitas
     * @param string $deskripsi  Detail penjelasan aktivitas
     */
    function catatAuditAdmin($conn, $admin_id, $target_user_id, $aktivitas, $deskripsi) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '-';

        // Sanitasi input
        $aktivitas = htmlspecialchars($aktivitas, ENT_QUOTES, 'UTF-8');
        $deskripsi = htmlspecialchars($deskripsi, ENT_QUOTES, 'UTF-8');

        // Cek apakah kolom admin_id, ip_address, user_agent ada di tabel
        $check = mysqli_query($conn, "SHOW COLUMNS FROM audit_log LIKE 'ip_address'");
        if ($check && mysqli_num_rows($check) > 0) {
            // Tabel sudah di-migrate
            $stmt = mysqli_prepare($conn,
                "INSERT INTO audit_log (user_id, aktivitas, deskripsi, ip_address, user_agent, admin_id)
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 'issssi',
                    $admin_id, $aktivitas, $deskripsi, $ip, $ua, $target_user_id
                );
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
        } else {
            // Fallback: tabel belum di-migrate, gunakan kolom standar
            $stmt = mysqli_prepare($conn,
                "INSERT INTO audit_log (user_id, aktivitas, deskripsi)
                 VALUES (?, ?, ?)"
            );
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 'iss',
                    $admin_id, $aktivitas, $deskripsi
                );
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
        }
    }
}

if (!function_exists('sanitizeInput')) {
    /**
     * Sanitasi input string untuk mencegah XSS.
     */
    function sanitizeInput($input) {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('validateCSRFToken')) {
    /**
     * Generate CSRF token.
     */
    function generateCSRFToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}
