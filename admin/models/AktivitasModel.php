<?php
/**
 * Aktivitas Model
 * Query audit log / aktivitas nasabah untuk admin.
 */

function getAktivitasList($conn, $search = '', $filter_aktivitas = '', $filter_tanggal_dari = '', $filter_tanggal_sampai = '', $page = 1, $per_page = 15) {
    $offset = ($page - 1) * $per_page;

    $where = "WHERE 1=1";
    $params = [];
    $types = '';

    if (!empty($search)) {
        $search_like = "%{$search}%";
        $where .= " AND (u.nama_lengkap LIKE ? OR u.email LIKE ? OR a.aktivitas LIKE ? OR a.deskripsi LIKE ?)";
        $params[] = $search_like;
        $params[] = $search_like;
        $params[] = $search_like;
        $params[] = $search_like;
        $types .= 'ssss';
    }

    if (!empty($filter_aktivitas)) {
        $where .= " AND a.aktivitas = ?";
        $params[] = $filter_aktivitas;
        $types .= 's';
    }

    if (!empty($filter_tanggal_dari)) {
        $where .= " AND DATE(a.created_at) >= ?";
        $params[] = $filter_tanggal_dari;
        $types .= 's';
    }

    if (!empty($filter_tanggal_sampai)) {
        $where .= " AND DATE(a.created_at) <= ?";
        $params[] = $filter_tanggal_sampai;
        $types .= 's';
    }

    // Count total
    $count_sql = "SELECT COUNT(*) AS total
                  FROM audit_log a
                  JOIN users u ON a.user_id = u.id
                  $where";
    $stmt = mysqli_prepare($conn, $count_sql);
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $total = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['total'];
    mysqli_stmt_close($stmt);

    // Fetch data
    $sql = "SELECT a.*, u.nama_lengkap, u.email, r.nama_role
            FROM audit_log a
            JOIN users u ON a.user_id = u.id
            JOIN roles r ON u.role_id = r.id
            $where
            ORDER BY a.created_at DESC
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

function getDistinctAktivitas($conn) {
    $stmt = mysqli_prepare($conn, "SELECT DISTINCT aktivitas FROM audit_log ORDER BY aktivitas ASC");
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row['aktivitas'];
    }
    mysqli_stmt_close($stmt);
    return $rows;
}
