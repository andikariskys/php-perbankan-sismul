<?php
/**
 * Nasabah Model
 * CRUD dan query data nasabah untuk admin.
 */

function getNasabahList($conn, $search = '', $page = 1, $per_page = 10) {
    $offset = ($page - 1) * $per_page;

    $where = "WHERE u.role_id = 2";
    $params = [];
    $types = '';

    if (!empty($search)) {
        $search_like = "%{$search}%";
        $where .= " AND (u.nama_lengkap LIKE ? OR u.email LIKE ? OR u.no_hp LIKE ?)";
        $params[] = $search_like;
        $params[] = $search_like;
        $params[] = $search_like;
        $types .= 'sss';
    }

    // Count total
    $count_sql = "SELECT COUNT(*) AS total FROM users u $where";
    $stmt = mysqli_prepare($conn, $count_sql);
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $total = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['total'];
    mysqli_stmt_close($stmt);

    // Fetch data
    $sql = "SELECT u.*,
                   (SELECT COUNT(*) FROM rekening r WHERE r.user_id = u.id) AS jumlah_rekening,
                   (SELECT COALESCE(SUM(r2.saldo), 0) FROM rekening r2 WHERE r2.user_id = u.id) AS total_saldo
            FROM users u
            $where
            ORDER BY u.created_at DESC
            LIMIT ? OFFSET ?";
    $params[] = $per_page;
    $params[] = $offset;
    $types .= 'ii';

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    mysqli_stmt_close($stmt);

    return ['data' => $rows, 'total' => (int) $total];
}

function getNasabahById($conn, $id) {
    $stmt = mysqli_prepare($conn,
        "SELECT u.*,
                (SELECT COUNT(*) FROM rekening r WHERE r.user_id = u.id) AS jumlah_rekening,
                (SELECT COALESCE(SUM(r2.saldo), 0) FROM rekening r2 WHERE r2.user_id = u.id) AS total_saldo
         FROM users u
         WHERE u.id = ? AND u.role_id = 2"
    );
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return $row;
}

function getPendingNasabah($conn) {
    $stmt = mysqli_prepare($conn,
        "SELECT u.*
         FROM users u
         WHERE u.role_id = 2 AND u.status_akun = 'Pending'
         ORDER BY u.created_at ASC"
    );
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    mysqli_stmt_close($stmt);
    return $rows;
}

function verifikasiAkun($conn, $user_id, $admin_id) {
    // Dapatkan status sebelum
    $stmt = mysqli_prepare($conn, "SELECT status_akun FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);
    $old = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    if (!$old) return false;

    $status_sebelum = $old['status_akun'];

    // Update status akun
    $stmt = mysqli_prepare($conn,
        "UPDATE users SET status_akun = 'Aktif', verified_at = NOW(), verified_by = ?, updated_at = NOW() WHERE id = ?"
    );
    mysqli_stmt_bind_param($stmt, 'ii', $admin_id, $user_id);
    $success = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if ($success) {
        // Simpan ke account_verifications
        $stmt = mysqli_prepare($conn,
            "INSERT INTO account_verifications (user_id, admin_id, status_sebelum, status_sesudah, keterangan)
             VALUES (?, ?, ?, 'Aktif', 'Akun diverifikasi oleh admin')"
        );
        mysqli_stmt_bind_param($stmt, 'iis', $user_id, $admin_id, $status_sebelum);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        // Simpan ke account_status_history
        $stmt = mysqli_prepare($conn,
            "INSERT INTO account_status_history (user_id, admin_id, status_sebelum, status_sesudah, keterangan)
             VALUES (?, ?, ?, 'Aktif', 'Verifikasi akun oleh admin')"
        );
        mysqli_stmt_bind_param($stmt, 'iis', $user_id, $admin_id, $status_sebelum);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    return $success;
}

function updateStatusAkun($conn, $user_id, $admin_id, $status_baru) {
    // Dapatkan status sebelum
    $stmt = mysqli_prepare($conn, "SELECT status_akun FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);
    $old = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    if (!$old) return false;

    $status_sebelum = $old['status_akun'];

    // Update status
    $stmt = mysqli_prepare($conn,
        "UPDATE users SET status_akun = ?, updated_at = NOW() WHERE id = ?"
    );
    mysqli_stmt_bind_param($stmt, 'si', $status_baru, $user_id);
    $success = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if ($success) {
        // Simpan riwayat
        $keterangan = "Status diubah dari $status_sebelum ke $status_baru oleh admin";
        $stmt = mysqli_prepare($conn,
            "INSERT INTO account_status_history (user_id, admin_id, status_sebelum, status_sesudah, keterangan)
             VALUES (?, ?, ?, ?, ?)"
        );
        mysqli_stmt_bind_param($stmt, 'iisss', $user_id, $admin_id, $status_sebelum, $status_baru, $keterangan);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    return $success;
}

function resetPasswordNasabah($conn, $user_id, $admin_id) {
    // Generate password sementara
    $temp_password = 'Temp' . bin2hex(random_bytes(4));
    $hashed = password_hash($temp_password, PASSWORD_DEFAULT);

    // Update password
    $stmt = mysqli_prepare($conn,
        "UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?"
    );
    mysqli_stmt_bind_param($stmt, 'si', $hashed, $user_id);
    $success = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if ($success) {
        // Simpan ke tabel password_resets
        $stmt = mysqli_prepare($conn,
            "INSERT INTO password_resets (user_id, admin_id, temp_password) VALUES (?, ?, ?)"
        );
        mysqli_stmt_bind_param($stmt, 'iis', $user_id, $admin_id, $temp_password);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        return $temp_password;
    }

    return false;
}
