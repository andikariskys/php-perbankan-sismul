<?php
/**
 * Dashboard Model
 * Query data statistik untuk dashboard admin.
 */

function getTotalNasabah($conn) {
    $stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS total FROM users WHERE role_id = 2");
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return (int) $row['total'];
}

function getTotalRekening($conn) {
    $stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS total FROM rekening");
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return (int) $row['total'];
}

function getTotalTransaksi($conn) {
    $stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS total FROM transaksi");
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return (int) $row['total'];
}

function getTotalSaldo($conn) {
    $stmt = mysqli_prepare($conn, "SELECT COALESCE(SUM(saldo), 0) AS total FROM rekening");
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return (float) $row['total'];
}

function getJumlahAkunAktif($conn) {
    $stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS total FROM users WHERE role_id = 2 AND status_akun = 'Aktif'");
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return (int) $row['total'];
}

function getJumlahAkunPending($conn) {
    $stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS total FROM users WHERE role_id = 2 AND status_akun = 'Pending'");
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return (int) $row['total'];
}

function getAktivitasTerbaru($conn, $limit = 10) {
    $stmt = mysqli_prepare($conn,
        "SELECT a.*, u.nama_lengkap, u.email
         FROM audit_log a
         JOIN users u ON a.user_id = u.id
         ORDER BY a.created_at DESC
         LIMIT ?"
    );
    mysqli_stmt_bind_param($stmt, 'i', $limit);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    mysqli_stmt_close($stmt);
    return $rows;
}
