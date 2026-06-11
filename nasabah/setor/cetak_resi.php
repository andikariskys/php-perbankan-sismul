<?php
session_start();
include "../../config/database.php";
include "../../helper/encryption.php";
include "../../helper/format.php";

// Cek login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php?pesan=belum_login');
    exit;
}

// Cek role
if ($_SESSION['nama_role'] !== 'Nasabah') {
    header('Location: ../../login.php?pesan=akses_ditolak');
    exit;
}

$user_id = $_SESSION['user_id'];

// Validasi parameter ID transaksi
$transaksi_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$transaksi_id) {
    header('Location: index.php');
    exit;
}

// Query data transaksi setor milik nasabah yang login
$query = "SELECT t.id, t.nominal, t.saldo_sebelum, t.saldo_sesudah, t.keterangan, t.created_at,
                 r.nomor_rekening_encrypted, r.jenis_rekening,
                 u.nama_lengkap, u.email
          FROM transaksi t
          JOIN rekening r ON t.rekening_id = r.id
          JOIN users u ON r.user_id = u.id
          WHERE t.id = ? AND r.user_id = ? AND t.jenis_transaksi = 'SETOR'";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ii", $transaksi_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Jika transaksi tidak ditemukan atau bukan milik user
if (mysqli_num_rows($result) === 0) {
    header('Location: index.php?pesan=transaksi_tidak_ditemukan');
    exit;
}

$transaksi = mysqli_fetch_assoc($result);

// Format nomor transaksi
$nomor_transaksi = 'TRX-' . str_pad($transaksi['id'], 6, '0', STR_PAD_LEFT);
$nomor_rekening = decrypt($transaksi['nomor_rekening_encrypted']);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resi Setor Dana - <?= $nomor_transaksi ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f0f2f5;
        }

        .resi-container {
            max-width: 500px;
            margin: 30px auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .resi-header {
            background: linear-gradient(135deg, #ffc107, #b8860b);
            color: white;
            padding: 24px;
            text-align: center;
        }

        .resi-header h4 {
            margin: 0;
            font-weight: 700;
        }

        .resi-header .resi-icon {
            font-size: 48px;
            margin-bottom: 10px;
        }

        .resi-body {
            padding: 24px;
        }

        .resi-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px dashed #e0e0e0;
        }

        .resi-row:last-child {
            border-bottom: none;
        }

        .resi-label {
            color: #6c757d;
            font-size: 14px;
        }

        .resi-value {
            font-weight: 600;
            text-align: right;
            font-size: 14px;
        }

        .resi-nominal {
            font-size: 28px;
            font-weight: 700;
            color: #198754;
            text-align: center;
            padding: 16px 0;
            border-bottom: 2px solid #e0e0e0;
            margin-bottom: 8px;
        }

        .resi-status {
            display: inline-block;
            background: #d4edda;
            color: #155724;
            padding: 4px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }

        .resi-footer {
            text-align: center;
            padding: 16px 24px;
            background: #f8f9fa;
            border-top: 1px solid #e0e0e0;
        }

        .resi-footer small {
            color: #6c757d;
        }

        /* Print styles */
        @media print {
            .no-print {
                display: none !important;
            }

            body {
                background: white;
                margin: 0;
                padding: 0;
            }

            .resi-container {
                box-shadow: none;
                border: 1px solid #000;
                margin: 0 auto;
            }
        }
    </style>
</head>

<body>

    <!-- Tombol Aksi (tidak dicetak) -->
    <div class="text-center mt-4 no-print">
        <button onclick="window.print()" class="btn btn-primary me-2">
            <i class="fas fa-print me-2"></i>Cetak Resi
        </button>
        <a href="index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>

    <!-- Resi -->
    <div class="resi-container">

        <!-- Header Resi -->
        <div class="resi-header">
            <div class="resi-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h4>Setor Dana Berhasil</h4>
            <p class="mb-0 mt-1 opacity-75" style="font-size: 14px;">
                <?= $nomor_transaksi ?>
            </p>
        </div>

        <!-- Body Resi -->
        <div class="resi-body">

            <!-- Nominal Besar -->
            <div class="resi-nominal">
                +<?= formatCurrency($transaksi['nominal']) ?>
            </div>

            <!-- Detail Transaksi -->
            <div class="resi-row">
                <span class="resi-label">No. Transaksi</span>
                <span class="resi-value"><?= $nomor_transaksi ?></span>
            </div>

            <div class="resi-row">
                <span class="resi-label">Tanggal</span>
                <span class="resi-value"><?= formatDate($transaksi['created_at']) ?></span>
            </div>

            <div class="resi-row">
                <span class="resi-label">Waktu</span>
                <span class="resi-value"><?= date('H:i:s', strtotime($transaksi['created_at'])) ?> WIB</span>
            </div>

            <div class="resi-row">
                <span class="resi-label">Nama Nasabah</span>
                <span class="resi-value"><?= htmlspecialchars($transaksi['nama_lengkap']) ?></span>
            </div>

            <div class="resi-row">
                <span class="resi-label">No. Rekening</span>
                <span class="resi-value"><code><?= $nomor_rekening ?></code></span>
            </div>

            <div class="resi-row">
                <span class="resi-label">Jenis Rekening</span>
                <span class="resi-value"><?= $transaksi['jenis_rekening'] ?></span>
            </div>

            <div class="resi-row">
                <span class="resi-label">Merchant</span>
                <span class="resi-value"><?= htmlspecialchars($transaksi['keterangan']) ?></span>
            </div>

            <div class="resi-row">
                <span class="resi-label">Saldo Sebelum</span>
                <span class="resi-value"><?= formatCurrency($transaksi['saldo_sebelum']) ?></span>
            </div>

            <div class="resi-row">
                <span class="resi-label">Saldo Sesudah</span>
                <span class="resi-value text-success"><?= formatCurrency($transaksi['saldo_sesudah']) ?></span>
            </div>

            <div class="resi-row">
                <span class="resi-label">Status</span>
                <span class="resi-value">
                    <span class="resi-status">
                        <i class="fas fa-check me-1"></i>Berhasil
                    </span>
                </span>
            </div>

        </div>

        <!-- Footer Resi -->
        <div class="resi-footer">
            <small>
                <i class="fas fa-university me-1"></i>
                Bank Multimedia &mdash; Resi ini merupakan bukti transaksi yang sah.<br>
                Dicetak pada: <?= date('d/m/Y H:i:s') ?> WIB
            </small>
        </div>

    </div>

    <!-- Tombol bawah (tidak dicetak) -->
    <div class="text-center my-4 no-print">
        <button onclick="window.print()" class="btn btn-outline-primary me-2">
            <i class="fas fa-print me-2"></i>Cetak Resi
        </button>
        <a href="index.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali ke Setor Dana
        </a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>
