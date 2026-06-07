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

$user_id = $_SESSION['user_id'];
$topup_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($topup_id <= 0) {
    header('Location: riwayat.php');
    exit;
}

$query_resi = "SELECT 
                    te.id,
                    te.nomor_tujuan,
                    te.nominal,
                    te.biaya_admin,
                    te.total_potongan,
                    te.created_at,
                    pe.nama_provider,
                    u.nama_lengkap
               FROM topup_ewallet te
               JOIN provider_ewallet pe ON te.provider_id = pe.id
               JOIN rekening r ON te.rekening_id = r.id
               JOIN users u ON r.user_id = u.id
               WHERE te.id = ?
               AND r.user_id = ?
               LIMIT 1";

$stmt_resi = mysqli_prepare($conn, $query_resi);
mysqli_stmt_bind_param($stmt_resi, "ii", $topup_id, $user_id);
mysqli_stmt_execute($stmt_resi);
$result_resi = mysqli_stmt_get_result($stmt_resi);

if (mysqli_num_rows($result_resi) === 0) {
    header('Location: riwayat.php');
    exit;
}

$resi = mysqli_fetch_assoc($result_resi);
$kode_resi = "TOPUP-" . str_pad($resi['id'], 6, "0", STR_PAD_LEFT);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Resi Top Up</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body {
            background-color: #f8f9fa;
        }

        .receipt {
            max-width: 520px;
            margin: 40px auto;
            background: white;
            border-radius: 12px;
            padding: 28px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
        }

        .receipt-title {
            border-bottom: 2px dashed #ccc;
            padding-bottom: 16px;
            margin-bottom: 20px;
        }

        .receipt-footer {
            border-top: 2px dashed #ccc;
            padding-top: 16px;
            margin-top: 20px;
        }

        @media print {
            .no-print {
                display: none;
            }

            body {
                background: white;
            }

            .receipt {
                box-shadow: none;
                margin: 0 auto;
            }
        }
    </style>
</head>

<body>

    <div class="receipt">
        <div class="text-center receipt-title">
            <h4 class="fw-bold text-success mb-1">
                <i class="fa-solid fa-building-columns me-1"></i>
                Bank Multimedia
            </h4>
            <p class="mb-0 text-muted">Resi Top Up E-Wallet</p>
        </div>

        <div class="mb-3 text-center">
            <span class="badge bg-success fs-6">BERHASIL</span>
        </div>

        <table class="table table-borderless">
            <tr>
                <td>Kode Resi</td>
                <td class="text-end fw-bold"><?= htmlspecialchars($kode_resi); ?></td>
            </tr>
            <tr>
                <td>Nama Nasabah</td>
                <td class="text-end"><?= htmlspecialchars($resi['nama_lengkap']); ?></td>
            </tr>
            <tr>
                <td>Tanggal</td>
                <td class="text-end"><?= date('d-m-Y H:i:s', strtotime($resi['created_at'])); ?></td>
            </tr>
            <tr>
                <td>Provider</td>
                <td class="text-end"><?= htmlspecialchars($resi['nama_provider']); ?></td>
            </tr>
            <tr>
                <td>Nomor Tujuan</td>
                <td class="text-end"><?= htmlspecialchars($resi['nomor_tujuan']); ?></td>
            </tr>
            <tr>
                <td>Nominal</td>
                <td class="text-end">Rp<?= number_format($resi['nominal'], 0, ',', '.'); ?></td>
            </tr>
            <tr>
                <td>Fee Admin</td>
                <td class="text-end">Rp<?= number_format($resi['biaya_admin'], 0, ',', '.'); ?></td>
            </tr>
            <tr class="fw-bold">
                <td>Total Potongan</td>
                <td class="text-end text-success">Rp<?= number_format($resi['total_potongan'], 0, ',', '.'); ?></td>
            </tr>
        </table>

        <div class="receipt-footer text-center">
            <p class="mb-1">Terima kasih telah menggunakan layanan kami.</p>
            <small class="text-muted">Simpan resi ini sebagai bukti transaksi.</small>
        </div>

        <div class="d-grid gap-2 mt-4 no-print">
            <button onclick="window.print()" class="btn btn-success">
                <i class="fa-solid fa-print me-1"></i>
                Cetak Resi
            </button>

            <a href="riwayat.php" class="btn btn-outline-success">
                <i class="fa-solid fa-clock-rotate-left me-1"></i>
                Kembali ke Riwayat
            </a>

            <a href="index.php" class="btn btn-outline-secondary">
                <i class="fa-solid fa-plus me-1"></i>
                Top Up Lagi
            </a>
        </div>
    </div>

</body>
</html>