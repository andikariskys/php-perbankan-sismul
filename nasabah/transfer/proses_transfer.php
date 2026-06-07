<?php
session_start();
include "../../config/database.php";
include "../../helper/encryption.php";

/** @var mysqli $conn */

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php?pesan=belum_login');
    exit;
}

if ($_SESSION['nama_role'] !== 'Nasabah') {
    header('Location: ../../login.php?pesan=akses_ditolak');
    exit;
}

if (!isset($_POST['transfer_submit'])) {
    header('Location: index.php');
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$rekening_pengirim_id = (int) filter_input(INPUT_POST, 'rekening_pengirim_id', FILTER_VALIDATE_INT);
$nomor_rekening_tujuan = trim((string) filter_input(INPUT_POST, 'nomor_rekening_tujuan', FILTER_UNSAFE_RAW));
$nominal = (float) filter_input(INPUT_POST, 'nominal', FILTER_VALIDATE_FLOAT);
$catatan = trim((string) filter_input(INPUT_POST, 'catatan', FILTER_UNSAFE_RAW));

if ($rekening_pengirim_id <= 0) {
    header('Location: index.php?pesan=rekening_pengirim_tidak_valid');
    exit;
}

if ($nominal <= 0) {
    header('Location: index.php?pesan=nominal_tidak_valid');
    exit;
}

if ($nomor_rekening_tujuan === '' || !preg_match('/^[0-9]{10,20}$/', $nomor_rekening_tujuan)) {
    header('Location: index.php?pesan=rekening_tujuan_tidak_valid');
    exit;
}

$query_rekening_pengirim = "SELECT id, saldo, nomor_rekening_encrypted FROM rekening WHERE id = ? AND user_id = ? AND status_rekening = 'Aktif' LIMIT 1";
$stmt_rekening_pengirim = mysqli_prepare($conn, $query_rekening_pengirim);
mysqli_stmt_bind_param($stmt_rekening_pengirim, "ii", $rekening_pengirim_id, $user_id);
mysqli_stmt_execute($stmt_rekening_pengirim);
$result_rekening_pengirim = mysqli_stmt_get_result($stmt_rekening_pengirim);

if (mysqli_num_rows($result_rekening_pengirim) === 0) {
    header('Location: index.php?pesan=rekening_pengirim_tidak_valid');
    exit;
}

$rekening_pengirim = mysqli_fetch_assoc($result_rekening_pengirim);
$saldo_pengirim_sebelum = (float) $rekening_pengirim['saldo'];

$query_rekening_tujuan = "SELECT id, user_id, nomor_rekening_encrypted, saldo FROM rekening WHERE status_rekening = 'Aktif'";
$result_rekening_tujuan = mysqli_query($conn, $query_rekening_tujuan);

$rekening_tujuan = null;
while ($row = mysqli_fetch_assoc($result_rekening_tujuan)) {
    $nomor_rekening = decrypt($row['nomor_rekening_encrypted']);

    if ($nomor_rekening === $nomor_rekening_tujuan) {
        $rekening_tujuan = $row;
        $rekening_tujuan['nomor_rekening'] = $nomor_rekening;
        break;
    }
}

if ($rekening_tujuan === null) {
    header('Location: index.php?pesan=rekening_tujuan_tidak_valid');
    exit;
}

if ((int) $rekening_tujuan['id'] === $rekening_pengirim_id) {
    header('Location: index.php?pesan=rekening_sama');
    exit;
}

if ($saldo_pengirim_sebelum < $nominal) {
    header('Location: index.php?pesan=saldo_kurang');
    exit;
}

$saldo_pengirim_sesudah = $saldo_pengirim_sebelum - $nominal;
$saldo_tujuan_sebelum = (float) $rekening_tujuan['saldo'];
$saldo_tujuan_sesudah = $saldo_tujuan_sebelum + $nominal;

mysqli_begin_transaction($conn);

try {
    $query_update_pengirim = "UPDATE rekening SET saldo = ? WHERE id = ?";
    $stmt_update_pengirim = mysqli_prepare($conn, $query_update_pengirim);
    mysqli_stmt_bind_param($stmt_update_pengirim, "di", $saldo_pengirim_sesudah, $rekening_pengirim_id);
    if (!mysqli_stmt_execute($stmt_update_pengirim)) {
        throw new Exception('Gagal memperbarui saldo pengirim');
    }

    $rekening_tujuan_id = (int) $rekening_tujuan['id'];
    $query_update_tujuan = "UPDATE rekening SET saldo = ? WHERE id = ?";
    $stmt_update_tujuan = mysqli_prepare($conn, $query_update_tujuan);
    mysqli_stmt_bind_param($stmt_update_tujuan, "di", $saldo_tujuan_sesudah, $rekening_tujuan_id);
    if (!mysqli_stmt_execute($stmt_update_tujuan)) {
        throw new Exception('Gagal memperbarui saldo tujuan');
    }

    $query_transfer = "INSERT INTO transfer (rekening_pengirim_id, rekening_penerima_id, nominal, catatan) VALUES (?, ?, ?, ?)";
    $stmt_transfer = mysqli_prepare($conn, $query_transfer);
    mysqli_stmt_bind_param($stmt_transfer, "iids", $rekening_pengirim_id, $rekening_tujuan_id, $nominal, $catatan);
    if (!mysqli_stmt_execute($stmt_transfer)) {
        throw new Exception('Gagal menyimpan transfer');
    }

    $transfer_id = mysqli_insert_id($conn);
    $keterangan_keluar = 'Transfer ke rekening ' . $rekening_tujuan['nomor_rekening'];
    $keterangan_masuk = 'Transfer dari rekening ' . decrypt($rekening_pengirim['nomor_rekening_encrypted']);

    $query_transaksi_keluar = "INSERT INTO transaksi (rekening_id, jenis_transaksi, nominal, saldo_sebelum, saldo_sesudah, keterangan) VALUES (?, 'TRANSFER_KELUAR', ?, ?, ?, ?)";
    $stmt_transaksi_keluar = mysqli_prepare($conn, $query_transaksi_keluar);
    mysqli_stmt_bind_param(
        $stmt_transaksi_keluar,
        "iddds",
        $rekening_pengirim_id,
        $nominal,
        $saldo_pengirim_sebelum,
        $saldo_pengirim_sesudah,
        $keterangan_keluar
    );
    if (!mysqli_stmt_execute($stmt_transaksi_keluar)) {
        throw new Exception('Gagal menyimpan transaksi keluar');
    }

    $query_transaksi_masuk = "INSERT INTO transaksi (rekening_id, jenis_transaksi, nominal, saldo_sebelum, saldo_sesudah, keterangan) VALUES (?, 'TRANSFER_MASUK', ?, ?, ?, ?)";
    $stmt_transaksi_masuk = mysqli_prepare($conn, $query_transaksi_masuk);
    mysqli_stmt_bind_param(
        $stmt_transaksi_masuk,
        "iddds",
        $rekening_tujuan_id,
        $nominal,
        $saldo_tujuan_sebelum,
        $saldo_tujuan_sesudah,
        $keterangan_masuk
    );
    if (!mysqli_stmt_execute($stmt_transaksi_masuk)) {
        throw new Exception('Gagal menyimpan transaksi masuk');
    }

    $aktivitas = 'Transfer Antar Rekening';
    $deskripsi = 'Transfer sebesar Rp' . number_format($nominal, 0, ',', '.') . ' dari rekening ' . $rekening_pengirim_id . ' ke rekening ' . $rekening_tujuan_id;

    $query_audit = "INSERT INTO audit_log (user_id, aktivitas, deskripsi) VALUES (?, ?, ?)";
    $stmt_audit = mysqli_prepare($conn, $query_audit);
    mysqli_stmt_bind_param($stmt_audit, "iss", $user_id, $aktivitas, $deskripsi);
    if (!mysqli_stmt_execute($stmt_audit)) {
        throw new Exception('Gagal menyimpan audit log');
    }

    mysqli_commit($conn);

    header('Location: cetak_resi.php?id=' . $transfer_id);
    exit;
} catch (Exception $e) {
    mysqli_rollback($conn);
    header('Location: index.php?pesan=gagal');
    exit;
}
