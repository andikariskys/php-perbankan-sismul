<?php
session_start();
include "../../config/database.php";
include "../../helper/format.php";

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php?pesan=belum_login');
    exit;
}

if ($_SESSION['nama_role'] !== 'Nasabah') {
    header('Location: ../../login.php?pesan=akses_ditolak');
    exit;
}

$user_id = $_SESSION['user_id'];
$stmt = mysqli_prepare($conn, "SELECT u.*, r.nama_role FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    header('Location: ../../login.php?pesan=akses_ditolak');
    exit;
}

$_SESSION['nama_lengkap'] = $user['nama_lengkap'];
$_SESSION['email'] = $user['email'];
$_SESSION['foto_profil'] = $user['foto_profil'];
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - Bank Multimedia</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">


</head>

<body class="d-flex flex-column min-vh-100 bg-light">

    
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
                        <a class="nav-link active"
                            href="../profil/index.php">
                            Profil
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link"
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

    
    <header class="bg-danger bg-gradient text-white py-5 shadow-sm">
        <div class="container">
            <h1 class="display-6 fw-bold">
                <i class="fas fa-user-circle me-2"></i>Profil Saya
            </h1>
            <p class="mb-0 opacity-75">
                Kelola informasi pribadi dan pengaturan keamanan akun Anda.
            </p>
        </div>
    </header>

    <main class="flex-grow-1">
        <div class="container py-4">

            
            <?php if (isset($_GET['pesan'])): ?>
                <?php
                $pesan = htmlspecialchars($_GET['pesan']);
                $alert_type = 'danger';
                $alert_icon = 'fa-exclamation-circle';
                $alert_text = '';

                switch ($pesan) {
                    case 'profil_berhasil':
                        $alert_type = 'success';
                        $alert_icon = 'fa-check-circle';
                        $alert_text = 'Profil berhasil diperbarui.';
                        break;
                    case 'profil_gagal':
                        $alert_text = 'Gagal memperbarui profil. Silakan coba lagi.';
                        break;
                    case 'password_berhasil':
                        $alert_type = 'success';
                        $alert_icon = 'fa-check-circle';
                        $alert_text = 'Password berhasil diubah.';
                        break;
                    case 'password_gagal':
                        $alert_text = 'Gagal mengubah password. Silakan coba lagi.';
                        break;
                    case 'password_lama_salah':
                        $alert_type = 'warning';
                        $alert_icon = 'fa-exclamation-triangle';
                        $alert_text = 'Password lama yang Anda masukkan salah.';
                        break;
                    case 'password_tidak_cocok':
                        $alert_type = 'warning';
                        $alert_icon = 'fa-exclamation-triangle';
                        $alert_text = 'Password baru dan konfirmasi password tidak cocok.';
                        break;
                    case 'password_pendek':
                        $alert_type = 'warning';
                        $alert_icon = 'fa-exclamation-triangle';
                        $alert_text = 'Password baru minimal 8 karakter.';
                        break;
                    case 'foto_berhasil':
                        $alert_type = 'success';
                        $alert_icon = 'fa-check-circle';
                        $alert_text = 'Foto profil berhasil diperbarui dan dikompresi.';
                        break;
                    case 'foto_gagal':
                        $alert_text = 'Gagal mengunggah foto profil. Silakan coba lagi.';
                        break;
                    case 'file_terlalu_besar':
                        $alert_type = 'warning';
                        $alert_icon = 'fa-exclamation-triangle';
                        $alert_text = 'Ukuran file terlalu besar. Maksimal 5MB.';
                        break;
                    case 'format_tidak_valid':
                        $alert_type = 'warning';
                        $alert_icon = 'fa-exclamation-triangle';
                        $alert_text = 'Format file tidak valid. Hanya JPG dan PNG yang diperbolehkan.';
                        break;
                    case 'field_kosong':
                        $alert_type = 'warning';
                        $alert_icon = 'fa-exclamation-triangle';
                        $alert_text = 'Semua field wajib diisi.';
                        break;
                    case 'no_hp_ada':
                        $alert_type = 'warning';
                        $alert_icon = 'fa-exclamation-triangle';
                        $alert_text = 'Nomor HP sudah digunakan oleh pengguna lain.';
                        break;
                }
                ?>

                <?php if ($alert_text): ?>
                    <div class="alert alert-<?= $alert_type ?> alert-dismissible fade show shadow-sm" role="alert">
                        <i class="fas <?= $alert_icon ?> me-2"></i>
                        <strong><?= $alert_type === 'success' ? 'Berhasil!' : 'Perhatian!' ?></strong> <?= $alert_text ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <div class="row g-4">
                <div class="col-lg-4">
                    <?php include "components/profile_card.php"; ?>
                </div>
                <div class="col-lg-8">
                    <?php include "components/edit_profil_form.php"; ?>
                    <?php include "components/update_password_form.php"; ?>
                </div>
            </div>

        </div>
    </main>

    <?php include "components/upload_modal.php"; ?>

    
    <footer class="bg-light border-top py-3">
        <div class="container-fluid text-center">
            <small>
                © <?= date('Y'); ?> Bank Multimedia
            </small>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Preview foto sebelum upload
        function previewFoto(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    var preview = document.getElementById('preview-foto');
                    var placeholder = document.getElementById('preview-placeholder');
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                    if (placeholder) {
                        placeholder.style.display = 'none';
                    }
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Toggle show/hide password
        document.querySelectorAll('.toggle-password').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var targetId = this.getAttribute('data-target');
                var input = document.getElementById(targetId);
                var icon = this.querySelector('i');

                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        });

        // Validasi form password di sisi client
        document.getElementById('formPassword').addEventListener('submit', function(e) {
            var baru = document.getElementById('password_baru').value;
            var konfirmasi = document.getElementById('konfirmasi_password').value;

            if (baru !== konfirmasi) {
                e.preventDefault();
                alert('Password baru dan konfirmasi password tidak cocok!');
                return false;
            }

            if (baru.length < 8) {
                e.preventDefault();
                alert('Password baru minimal 8 karakter!');
                return false;
            }
        });

        // Auto-dismiss alert setelah 5 detik
        setTimeout(function() {
            var alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                var bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>

</body>

</html>