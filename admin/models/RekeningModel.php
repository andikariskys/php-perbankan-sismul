<?php
/**
 * Rekening Model
 * Query data rekening untuk admin.
 */

function getRekeningList($conn, $search = '', $filter_status = '', $page = 1, $per_page = 10) {
    $offset = ($page - 1) * $per_page;

    $where = "WHERE 1=1";
    $params = [];
    $types = '';

    if (!empty($search)) {
        $search_like = "%{$search}%";
        $where .= " AND (u.nama_lengkap LIKE ? OR u.email LIKE ? OR r.nomor_rekening_encrypted LIKE ?)";
        $params[] = $search_like;
        $params[] = $search_like;
        $params[] = $search_like;
        $types .= 'sss';
    }

    if (!empty($filter_status) && in_array($filter_status, ['Aktif', 'Blokir'])) {
        $where .= " AND r.status_rekening = ?";
        $params[] = $filter_status;
        $types .= 's';
    }

    // Count total
    $count_sql = "SELECT COUNT(*) AS total
                  FROM rekening r
                  JOIN users u ON r.user_id = u.id
                  $where";
    $stmt = mysqli_prepare($conn, $count_sql);
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $total = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['total'];
    mysqli_stmt_close($stmt);

    // Fetch data
    $sql = "SELECT r.*, u.nama_lengkap, u.email
            FROM rekening r
            JOIN users u ON r.user_id = u.id
            $where
            ORDER BY r.created_at DESC
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

function getRekeningById($conn, $id) {
    $stmt = mysqli_prepare($conn,
        "SELECT r.*, u.nama_lengkap, u.email, u.no_hp, u.foto_profil
         FROM rekening r
         JOIN users u ON r.user_id = u.id
         WHERE r.id = ?"
    );
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return $row;
}

function getRekeningByUserId($conn, $user_id) {
    $stmt = mysqli_prepare($conn,
        "SELECT r.*
         FROM rekening r
         WHERE r.user_id = ?
         ORDER BY r.created_at DESC"
    );
    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    mysqli_stmt_close($stmt);
    return $rows;
}
