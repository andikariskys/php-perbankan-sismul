<?php
session_start();
include "../../config/database.php";

// ===================================================================
// CORE KEAMANAN DATA: AES-256-CBC
// ===================================================================
define('ENCRYPT_KEY', 'b4nk_mub4m4d1y4h_sur4k4rt4_32_b1t'); // 32 Karakter
define('ENCRYPT_IV', '1234567890123456');                 // 16 Karakter
define('ENCRYPT_METHOD', 'aes-256-cbc');

function enkripsiNoRek($nomor_rekening) {
    return openssl_encrypt($nomor_rekening, ENCRYPT_METHOD, ENCRYPT_KEY, 0, ENCRYPT_IV);
}

function dekripsiNoRek($nomor_rekening_encrypted) {
    return openssl_decrypt($nomor_rekening_encrypted, ENCRYPT_METHOD, ENCRYPT_KEY, 0, ENCRYPT_IV);
}

if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php?pesan=belum_login');
    exit;
}

if ($_SESSION['nama_role'] !== 'Nasabah') {
    header('Location: /login.php?pesan=akses_ditolak');
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informasi Rekening</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .hero {
            background: linear-gradient(135deg, #0dcaf0, #055160);
            color: white;
        }
    </style>
</head>

<body class="d-flex flex-column min-vh-100">

    <nav class="navbar navbar-expand-lg navbar-dark bg-success">
        <div class="container-fluid">

            <a class="navbar-brand fw-bold" href="#">
                Bank Multimedia
            </a>

            <button class="navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navbarNasabah">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNasabah">

                <ul class="navbar-nav me-auto">

                    <li class="nav-item">
                        <a class="nav-link"
                            href="../dashboard/index.php">
                            Dashboard
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link"
                            href="../profil/index.php">
                            Profil
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link active"
                            href="../rekening/index.php">
                            Rekening
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link"
                            href="../riwayat/index.php">
                            Riwayat
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link"
                            href="../setor/index.php">
                            Setor
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link"
                            href="../tarik/index.php">
                            Tarik
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link"
                            href="../transfer/index.php">
                            Transfer
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link"
                            href="../topup/index.php">
                            Top Up
                        </a>
                    </li>

                </ul>

                <a href="../../logout.php"
                    class="btn btn-light btn-sm">
                    Logout
                </a>

            </div>
        </div>
    </nav>

    <header class="hero py-5 shadow-sm">
        <div class="container">
            <h1 class="display-6 fw-bold">
                Informasi Rekening
            </h1>
            <p class="mb-0 opacity-75">
                Detail saldo dan status rekening aktif Anda.
            </p>
        </div>
    </header>

    <main class="flex-grow-1">
        <div class="container py-4">

            <?php
            // Ambil data user ID dari session login
            $user_id = $_SESSION['user_id'];

            // Ambil nama lengkap dari database untuk kebutuhan display
            $user_query = mysqli_query($conn, "SELECT nama_lengkap FROM users WHERE id = '$user_id'");
            $user_data = mysqli_fetch_assoc($user_query);
            $nama_user = $user_data['nama_lengkap'] ?? 'Nasabah';

            // Ambil data rekening nasabah
            $rekening_query = mysqli_query($conn, "SELECT * FROM rekening WHERE user_id = '$user_id'");
            $jumlah_rekening = mysqli_num_rows($rekening_query);

            if ($jumlah_rekening > 0) {
                // -----------------------------------------------------------------
                // KONDISI A: JIKA SUDAH PUNYA REKENING -> DEKRIPSI & TAMPILKAN
                // -----------------------------------------------------------------
                echo '<div class="row justify-content-center">';
                while ($row = mysqli_fetch_assoc($rekening_query)) {
                    
                    // PROSES DEKRIPSI
                    $no_rek_asli = dekripsiNoRek($row['nomor_rekening_encrypted']);
                    
                    $saldo_format = "Rp " . number_format($row['saldo'], 2, ',', '.');
                    $is_aktif = ($row['status_rekening'] === 'Aktif');
                    
                    echo '
                    <div class="col-md-6 mb-4">
                        <div class="card shadow border-0 overflow-hidden">
                            <div class="card-body bg-dark text-white p-4" style="background: linear-gradient(135deg, #1f2937, #111827);">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="card-title text-success fw-bold mb-0">
                                        <i class="fa-solid fa-building-columns me-2"></i>' . htmlspecialchars($row['jenis_rekening']) . '
                                    </h5>
                                    <span class="badge ' . ($is_aktif ? 'bg-success' : 'bg-danger') . ' pb-1">
                                        ' . htmlspecialchars($row['status_rekening']) . '
                                    </span>
                                </div>
                                <div class="mb-4 mt-3">
                                    <small class="text-muted d-block text-uppercase tracking-wider">Nomor Rekening</small>
                                    <h3 class="font-monospace tracking-widest my-1">' . htmlspecialchars($no_rek_asli) . '</h3>
                                </div>
                                <div class="d-flex justify-content-between align-items-end">
                                    <div>
                                        <small class="text-muted d-block">Pemilik Rekening</small>
                                        <span class="fw-semibold text-uppercase">' . htmlspecialchars($nama_user) . '</span>
                                    </div>
                                    <div class="text-end">
                                        <small class="text-muted d-block">Total Saldo</small>
                                        <span class="fs-4 fw-bold text-success">' . $saldo_format . '</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>';
                }
                echo '</div>';

            } else {
                // -----------------------------------------------------------------
                // KONDISI B: JIKA BELUM PUNYA REKENING -> TAMPILKAN FORM REGISTRASI
                // -----------------------------------------------------------------
                echo '
                <div class="row justify-content-center">
                    <div class="col-md-6">
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-white text-center py-4 border-0">
                                <div class="icon-box bg-success-subtle text-success rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                                    <i class="fa-solid fa-wallet fs-3"></i>
                                </div>
                                <h4 class="fw-bold text-dark mb-1">Buka Rekening Baru</h4>
                                <p class="text-muted small mb-0">Halo ' . htmlspecialchars($nama_user) . ', Anda belum memiliki rekening terdaftar.</p>
                            </div>
                            <div class="card-body px-4 pb-4">
                                <form action="proses_rekening.php" method="POST">
                                    <div class="mb-4">
                                        <label class="form-label fw-semibold text-secondary">Pilih Jenis Tabungan</label>
                                        <div class="form-check p-3 border rounded mb-2">
                                            <input class="form-check-input ms-0 me-2" type="radio" name="jenis_rekening" id="tabungan" value="Tabungan" checked>
                                            <label class="form-check-label fw-medium" for="tabungan">
                                                Tabungan Reguler <small class="text-muted d-block font-normal">Cocok untuk kebutuhan transaksi harian.</small>
                                            </label>
                                        </div>
                                        <div class="form-check p-3 border rounded mb-2">
                                            <input class="form-check-input ms-0 me-2" type="radio" name="jenis_rekening" id="giro" value="Giro">
                                            <label class="form-check-label fw-medium" for="giro">
                                                Giro Rekening <small class="text-muted d-block font-normal">Pilihan tepat untuk skala bisnis besar.</small>
                                            </label>
                                        </div>
                                    </div>
                                    <button type="submit" name="request_rekening" class="btn btn-success w-100 py-2.5 fw-bold shadow-sm">
                                        <i class="fa-solid fa-circle-check me-2"></i>Aktivasi Rekening Sekarang
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>';
            }
            ?>

        </div>
    </main>

    <footer class="bg-light border-top py-3">
        <div class="container-fluid text-center">
            <small>
                © <?= date('Y'); ?> Bank Multimedia
            </small>
        </div>
    </footer >

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>