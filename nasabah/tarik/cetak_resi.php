<?php
session_start();

if(!isset($_SESSION['user_id']) || $_SESSION['nama_role'] !== 'Nasabah'){
    header('Location: ../../login.php');
    exit;
}

require_once '../../config/database.php';
require_once '../../helper/format.php';
require_once '../../helper/encryption.php';

$transaksi_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

if(!$transaksi_id){
    die("ID Transaksi tidak valid!");
}

$user_id = $_SESSION['user_id'];

// Ambil data transaksi beserta rekening
$query_resi = "SELECT t.*, r.nomor_rekening_encrypted FROM transaksi t 
               JOIN rekening r ON t.rekening_id = r.id 
               WHERE t.id = ? AND r.user_id = ? AND t.jenis_transaksi = 'TARIK'";
$stmt = mysqli_prepare($conn, $query_resi);
mysqli_stmt_bind_param($stmt, "ii", $transaksi_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if(mysqli_num_rows($result) === 0){
    die("Resi tidak ditemukan atau Anda tidak memiliki akses.");
}

$resi = mysqli_fetch_assoc($result);

// Decrypt nomor rekening menggunakan helper
$no_rekening_asli = decrypt($resi['nomor_rekening_encrypted']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resi Tarik Dana - <?= htmlspecialchars($resi['id']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .resi-box {
            max-width: 400px;
            margin: 50px auto;
            border: 1px dashed #000;
            padding: 20px;
        }
        @media print {
            .no-print { display: none; }
            .resi-box { border: none; margin: 0; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="container">
        <div class="resi-box text-center">
            <h4>BANK MULTIMEDIA</h4>
            <p>Universitas Muhammadiyah Surakarta</p>
            <hr>
            <h5>RESI TARIK DANA</h5>
            <div class="text-start mt-3">
                <p><strong>No Transaksi :</strong> TRX-<?= htmlspecialchars($resi['id']); ?></p>
                <p><strong>Tanggal :</strong> <?= ucwords(formatDate($resi['created_at'])) . date(' H:i:s', strtotime($resi['created_at'])); ?></p>
                <p><strong>Nama Nasabah :</strong> <?= htmlspecialchars($_SESSION['nama_lengkap']); ?></p>
                <p><strong>No Rekening :</strong> <?= htmlspecialchars($no_rekening_asli); ?></p>
                <hr>
                <p class="mb-1">Saldo Awal: <?= formatCurrency($resi['saldo_sebelum']); ?></p>
                <p class="mb-1">Saldo Akhir: <?= formatCurrency($resi['saldo_sesudah']); ?></p>
                <p class="mb-1">Keterangan: <?= htmlspecialchars($resi['keterangan']); ?></p>
                <hr>
                <h5><strong>Nominal Ditarik : <?= formatCurrency($resi['nominal']); ?></strong></h5>
            </div>
            <hr>
            <p class="mt-3">Terima kasih telah bertransaksi menggunakan Bank Multimedia.</p>
        </div>
        
        <div class="text-center mt-3 no-print">
            <button onclick="window.print()" class="btn btn-primary">Cetak Ulang</button>
            <a href="index.php" class="btn btn-secondary">Kembali</a>
        </div>
    </div>
</body>
</html>