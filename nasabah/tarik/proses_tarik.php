<?php
session_start();

if(!isset($_SESSION['user_id']) || $_SESSION['nama_role'] !== 'Nasabah'){
    header('Location: ../../login.php');
    exit;
}

require_once '../../config/database.php';
require_once '../../helper/format.php';

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    // Ambil data rekening_id yang dipilih dari form drop-down
    $rekening_id = filter_input(INPUT_POST, 'rekening_id', FILTER_SANITIZE_NUMBER_INT);
    $nominal = htmlspecialchars(trim($_POST['nominal']));
    $nominal = filter_var($nominal, FILTER_SANITIZE_NUMBER_INT);
    $password_input = $_POST['password'];
    $user_id = $_SESSION['user_id'];

    if(!$rekening_id || !is_numeric($nominal) || $nominal <= 0){
        $_SESSION['pesan_error'] = "Input transaksi atau rekening tidak valid!";
        header('Location: index.php');
        exit;
    }

    // 1. Validasi Password Akun Pengguna
    $query_user = "SELECT password FROM users WHERE id = ?";
    $stmt_user = mysqli_prepare($conn, $query_user);
    mysqli_stmt_bind_param($stmt_user, "i", $user_id);
    mysqli_stmt_execute($stmt_user);
    $result_user = mysqli_stmt_get_result($stmt_user);
    $user_data = mysqli_fetch_assoc($result_user);

    if(!password_verify($password_input, $user_data['password'])){
        $_SESSION['pesan_error'] = "Password konfirmasi yang Anda masukkan salah!";
        header('Location: index.php');
        exit;
    }

    // 2. Validasi Kepemilikan Rekening (Mencegah IDOR / manipulasi ID Rekening dari Inspect Element)
    $query_rek = "SELECT id, saldo FROM rekening WHERE id = ? AND user_id = ? AND status_rekening = 'Aktif'";
    $stmt_rek = mysqli_prepare($conn, $query_rek);
    mysqli_stmt_bind_param($stmt_rek, "ii", $rekening_id, $user_id);
    mysqli_stmt_execute($stmt_rek);
    $result_rek = mysqli_stmt_get_result($stmt_rek);
    
    if(mysqli_num_rows($result_rek) === 0){
        $_SESSION['pesan_error'] = "Rekening tidak ditemukan atau status tidak aktif!";
        header('Location: index.php');
        exit;
    }

    $rekening = mysqli_fetch_assoc($result_rek);
    $saldo_sebelum = $rekening['saldo'];

    // 3. Validasi Kecukupan Saldo
    if($saldo_sebelum < $nominal){
        $_SESSION['pesan_error'] = "Saldo rekening yang dipilih tidak mencukupi!";
        header('Location: index.php');
        exit;
    }

    $saldo_sesudah = $saldo_sebelum - $nominal;

    mysqli_begin_transaction($conn);

    try {
        // A. Update Saldo Rekening Spesifik yang Dipilih
        $query_update = "UPDATE rekening SET saldo = ? WHERE id = ?";
        $stmt_update = mysqli_prepare($conn, $query_update);
        mysqli_stmt_bind_param($stmt_update, "di", $saldo_sesudah, $rekening_id);
        mysqli_stmt_execute($stmt_update);

        // B. Insert ke Tabel Transaksi
        $jenis_transaksi = 'TARIK';
        $keterangan = "Tarik tunai via web";
        $query_insert = "INSERT INTO transaksi (rekening_id, jenis_transaksi, nominal, saldo_sebelum, saldo_sesudah, keterangan) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt_insert = mysqli_prepare($conn, $query_insert);
        mysqli_stmt_bind_param($stmt_insert, "isddds", $rekening_id, $jenis_transaksi, $nominal, $saldo_sebelum, $saldo_sesudah, $keterangan);
        mysqli_stmt_execute($stmt_insert);
        
        $id_transaksi_baru = mysqli_insert_id($conn);

        // C. Insert ke Tabel Audit Log
        $aktivitas = "Tarik Dana";
        $deskripsi = "Nasabah melakukan penarikan dana sebesar " . formatCurrency($nominal) . " pada ID Rekening: " . $rekening_id;
        $query_audit = "INSERT INTO audit_log (user_id, aktivitas, deskripsi) VALUES (?, ?, ?)";
        $stmt_audit = mysqli_prepare($conn, $query_audit);
        mysqli_stmt_bind_param($stmt_audit, "iss", $user_id, $aktivitas, $deskripsi);
        mysqli_stmt_execute($stmt_audit);

        mysqli_commit($conn);

        $_SESSION['pesan_sukses'] = "Tarik dana berhasil diproses!";
        header('Location: cetak_resi.php?id=' . $id_transaksi_baru);
        exit;

    } catch (Exception $e) {
        mysqli_rollback($conn);
        $_SESSION['pesan_error'] = "Gagal memproses penarikan dana akibat gangguan sistem.";
        header('Location: index.php');
        exit;
    }
} else {
    header('Location: index.php');
    exit;
}
?>