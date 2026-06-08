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

$user_id = (int) $_SESSION['user_id'];
$transfer_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($transfer_id <= 0) {
    header('Location: riwayat.php');
    exit;
}

$query_resi = "SELECT 
                    t.id,
                    t.nominal,
                    t.catatan,
                    t.created_at,
                    rp.id AS rekening_pengirim_id,
                    rp.nomor_rekening_encrypted AS nomor_pengirim_encrypted,
                    rt.id AS rekening_penerima_id,
                    rt.nomor_rekening_encrypted AS nomor_penerima_encrypted,
                    up.nama_lengkap AS nama_pengirim,
                    ut.nama_lengkap AS nama_penerima,
                    rp.user_id AS user_pengirim_id,
                    rt.user_id AS user_penerima_id
                FROM transfer t
                JOIN rekening rp ON t.rekening_pengirim_id = rp.id
                JOIN rekening rt ON t.rekening_penerima_id = rt.id
                JOIN users up ON rp.user_id = up.id
                JOIN users ut ON rt.user_id = ut.id
                WHERE t.id = ?
                AND (rp.user_id = ? OR rt.user_id = ?)
                LIMIT 1";

$stmt_resi = mysqli_prepare($conn, $query_resi);
mysqli_stmt_bind_param($stmt_resi, "iii", $transfer_id, $user_id, $user_id);
mysqli_stmt_execute($stmt_resi);
$result_resi = mysqli_stmt_get_result($stmt_resi);

if (mysqli_num_rows($result_resi) === 0) {
    header('Location: riwayat.php');
    exit;
}

$resi = mysqli_fetch_assoc($result_resi);
$resi['nomor_pengirim'] = decrypt($resi['nomor_pengirim_encrypted']);
$resi['nomor_penerima'] = decrypt($resi['nomor_penerima_encrypted']);
$kode_resi = 'TRF-' . str_pad((string) $resi['id'], 6, '0', STR_PAD_LEFT);
$arah = ((int) $resi['user_pengirim_id'] === $user_id) ? 'Transfer Keluar' : 'Transfer Masuk';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Resi Transfer</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .receipt {
            max-width: 560px;
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
            <p class="mb-0 text-muted">Resi Transfer Rekening</p>
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
                <td>Nama Pengirim</td>
                <td class="text-end"><?= htmlspecialchars($resi['nama_pengirim']); ?></td>
            </tr>
            <tr>
                <td>Nama Penerima</td>
                <td class="text-end"><?= htmlspecialchars($resi['nama_penerima']); ?></td>
            </tr>
            <tr>
                <td>Rekening Pengirim</td>
                <td class="text-end"><?= htmlspecialchars($resi['nomor_pengirim']); ?></td>
            </tr>
            <tr>
                <td>Rekening Penerima</td>
                <td class="text-end"><?= htmlspecialchars($resi['nomor_penerima']); ?></td>
            </tr>
            <tr>
                <td>Tanggal</td>
                <td class="text-end"><?= date('d-m-Y H:i:s', strtotime($resi['created_at'])); ?></td>
            </tr>
            <tr>
                <td>Jenis Transaksi</td>
                <td class="text-end"><?= htmlspecialchars($arah); ?></td>
            </tr>
            <tr>
                <td>Nominal</td>
                <td class="text-end">Rp<?= number_format((float) $resi['nominal'], 0, ',', '.'); ?></td>
            </tr>
            <tr>
                <td>Catatan</td>
                <td class="text-end"><?= htmlspecialchars($resi['catatan'] ?: '-'); ?></td>
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
                Transfer Lagi
            </a>
        </div>
    </div>
</body>
</html>
