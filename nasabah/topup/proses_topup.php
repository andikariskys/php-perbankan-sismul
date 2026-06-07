<?php
session_start();
include "../../config/database.php";

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php?pesan=belum_login');
    exit;
}

if ($_SESSION['nama_role'] !== 'Nasabah') {
    header('Location: ../../login.php?pesan=akses_ditolak');
    exit;
}

if (!isset($_POST['topup_submit'])) {
    header('Location: index.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$provider_id = (int) $_POST['provider_id'];
$nomor_tujuan = htmlspecialchars(trim($_POST['nomor_tujuan']));
$nominal = (float) $_POST['nominal'];

if ($provider_id <= 0) {
    header('Location: index.php?pesan=provider_tidak_valid');
    exit;
}

if ($nominal < 10000) {
    header('Location: index.php?pesan=nominal_tidak_valid');
    exit;
}

if (empty($nomor_tujuan) || !preg_match('/^[0-9]{10,15}$/', $nomor_tujuan)) {
    header('Location: index.php?pesan=nomor_tidak_valid');
    exit;
}

$query_provider = "SELECT * FROM provider_ewallet 
                   WHERE id = ? 
                   AND nama_provider IN ('OVO', 'DANA', 'GoPay', 'ShopeePay')";
$stmt_provider = mysqli_prepare($conn, $query_provider);
mysqli_stmt_bind_param($stmt_provider, "i", $provider_id);
mysqli_stmt_execute($stmt_provider);
$result_provider = mysqli_stmt_get_result($stmt_provider);

if (mysqli_num_rows($result_provider) === 0) {
    header('Location: index.php?pesan=provider_tidak_valid');
    exit;
}

$provider = mysqli_fetch_assoc($result_provider);
$nama_provider = $provider['nama_provider'];
$biaya_admin = (float) $provider['biaya_admin'];
$total_potongan = $nominal + $biaya_admin;

$query_rekening = "SELECT * FROM rekening 
                   WHERE user_id = ? 
                   AND status_rekening = 'Aktif' 
                   LIMIT 1";
$stmt_rekening = mysqli_prepare($conn, $query_rekening);
mysqli_stmt_bind_param($stmt_rekening, "i", $user_id);
mysqli_stmt_execute($stmt_rekening);
$result_rekening = mysqli_stmt_get_result($stmt_rekening);

if (mysqli_num_rows($result_rekening) === 0) {
    header('Location: index.php?pesan=rekening_tidak_ada');
    exit;
}

$rekening = mysqli_fetch_assoc($result_rekening);

$rekening_id = $rekening['id'];
$saldo_sebelum = (float) $rekening['saldo'];

if ($saldo_sebelum < $total_potongan) {
    header('Location: index.php?pesan=saldo_kurang');
    exit;
}

$saldo_sesudah = $saldo_sebelum - $total_potongan;

mysqli_begin_transaction($conn);

try {
    $query_update_saldo = "UPDATE rekening 
                           SET saldo = ? 
                           WHERE id = ?";
    $stmt_update_saldo = mysqli_prepare($conn, $query_update_saldo);
    mysqli_stmt_bind_param($stmt_update_saldo, "di", $saldo_sesudah, $rekening_id);
    mysqli_stmt_execute($stmt_update_saldo);

    $query_topup = "INSERT INTO topup_ewallet 
                    (rekening_id, provider_id, nomor_tujuan, nominal, biaya_admin, total_potongan) 
                    VALUES (?, ?, ?, ?, ?, ?)";
    $stmt_topup = mysqli_prepare($conn, $query_topup);
    mysqli_stmt_bind_param(
        $stmt_topup,
        "iisddd",
        $rekening_id,
        $provider_id,
        $nomor_tujuan,
        $nominal,
        $biaya_admin,
        $total_potongan
    );
    mysqli_stmt_execute($stmt_topup);

    $topup_id = mysqli_insert_id($conn);

    $keterangan = "Top up " . $nama_provider . " ke nomor " . $nomor_tujuan .
                  " dengan fee Rp" . number_format($biaya_admin, 0, ',', '.');

    $query_transaksi = "INSERT INTO transaksi 
                        (rekening_id, jenis_transaksi, nominal, saldo_sebelum, saldo_sesudah, keterangan) 
                        VALUES (?, 'TOPUP_EWALLET', ?, ?, ?, ?)";
    $stmt_transaksi = mysqli_prepare($conn, $query_transaksi);
    mysqli_stmt_bind_param(
        $stmt_transaksi,
        "iddds",
        $rekening_id,
        $nominal,
        $saldo_sebelum,
        $saldo_sesudah,
        $keterangan
    );
    mysqli_stmt_execute($stmt_transaksi);

    $aktivitas = "Top Up E-Wallet";
    $deskripsi = "Nasabah melakukan top up " . $nama_provider .
                 " sebesar Rp" . number_format($nominal, 0, ',', '.') .
                 " ke nomor " . $nomor_tujuan .
                 ". Fee Rp" . number_format($biaya_admin, 0, ',', '.') .
                 ". Total potongan Rp" . number_format($total_potongan, 0, ',', '.');

    $query_audit = "INSERT INTO audit_log 
                    (user_id, aktivitas, deskripsi) 
                    VALUES (?, ?, ?)";
    $stmt_audit = mysqli_prepare($conn, $query_audit);
    mysqli_stmt_bind_param($stmt_audit, "iss", $user_id, $aktivitas, $deskripsi);
    mysqli_stmt_execute($stmt_audit);

    mysqli_commit($conn);

    header('Location: cetak_resi.php?id=' . $topup_id);
    exit;

} catch (Exception $e) {
    mysqli_rollback($conn);
    header('Location: index.php?pesan=gagal');
    exit;
}
?>