<?php
session_start();
include 'config/database.php';

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['nama_role'] == 'Admin') {
        header('Location: admin/dashboard/index.php');
    } else {
        header('Location: nasabah/dashboard/index.php');
    }
    exit;
}

if (isset($_POST['login_submit'])) {
    $email = $_POST['login_email'];
    $password = $_POST['login_password'];

    $query = "SELECT u.*, r.nama_role 
              FROM users u 
              JOIN roles r ON u.role_id = r.id 
              WHERE u.email = '$email'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);

        if (password_verify($password, $user['password'])) {
            if ($user['status_akun'] == 'Aktif') {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
                $_SESSION['nama_role'] = $user['nama_role'];

                // Log login
                mysqli_query($conn, "INSERT INTO login_log (user_id) VALUES ({$user['id']})");

                if ($user['nama_role'] == 'Admin') {
                    header('Location: admin/dashboard/index.php');
                } else {
                    header('Location: nasabah/dashboard/index.php');
                }
                exit;
            } elseif ($user['status_akun'] == 'Pending') {
                header("Location: login.php?pesan=pending");
                exit;
            } else {
                header("Location: login.php?pesan=nonaktif");
                exit;
            }
        } else {
            header("Location: login.php?pesan=gagal");
            exit;
        }
    } else {
        header("Location: login.php?pesan=gagal");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Nasabah - Bank Sismul</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
        }

        .register-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .register-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
            background: #fff;
        }

        .btn-primary {
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
        }
    </style>
</head>

<body class="min-vh-100 d-flex align-items-center justify-content-center">
    <div class="container d-flex align-items-center justify-content-center p-3">
        <div class="card p-4" style="max-width:420px; width:100%; border-radius:12px; box-shadow:0 8px 24px rgba(0,0,0,0.08);">
            <div class="text-center mb-4">
                <a href="index.php" class="text-decoration-none">
                    <h3 class="fw-bold text-primary"><i class="fas fa-university me-2"></i>BANK SISMUL</h3>
                </a>
                <p class="text-muted">Masuk ke akun Anda</p>
            </div>

            <?php
            if (isset($_GET['pesan'])) {
                $pesan = $_GET['pesan'];
                if ($pesan == 'gagal') { ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>Gagal!</strong> Email atau kata sandi salah.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php } else if ($pesan == 'belum_login') { ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>Perhatian!</strong> Anda harus masuk terlebih dahulu.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php } elseif ($pesan == 'akses_ditolak') { ?>
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <strong>Akses Ditolak!</strong> Anda tidak memiliki izin untuk mengakses halaman ini.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php } elseif ($pesan == 'berhasil_daftar') { ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <strong>Berhasil!</strong> Pendaftaran berhasil. Silakan tunggu aktivasi dari admin.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php } elseif ($pesan == 'pending') { ?>
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <strong>Informasi!</strong> Akun Anda masih menunggu aktivasi admin.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php } elseif ($pesan == 'nonaktif') { ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>Nonaktif!</strong> Akun Anda telah dinonaktifkan.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
            <?php }
            }
            ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Alamat Email</label>
                    <input type="email" name="login_email" class="form-control" required placeholder="name@example.com" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Kata Sandi</label>
                    <input type="password" name="login_password" class="form-control" required placeholder="Masukkan kata sandi">
                </div>
                <button type="submit" name="login_submit" class="btn btn-primary w-100">Masuk</button>
                <div class="text-center mt-3">
                    <a href="register.php" class="text-decoration-none">Belum punya akun? Daftar</a>
                </div>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>