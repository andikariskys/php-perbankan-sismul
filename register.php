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

if (isset($_POST['register_submit'])) {
    $nama_lengkap = $_POST['nama_lengkap'];
    $email = $_POST['email'];
    $no_hp = $_POST['no_hp'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Check if email or no_hp already exists
    $check_query = "SELECT * FROM users WHERE email = '$email' OR no_hp = '$no_hp'";
    $check_result = mysqli_query($conn, $check_query);

    if (mysqli_num_rows($check_result) > 0) {
        $existing_user = mysqli_fetch_assoc($check_result);
        if ($existing_user['email'] == $email) {
            header("Location: register.php?pesan=email_ada");
            exit;
        } elseif ($existing_user['no_hp'] == $no_hp) {
            header("Location: register.php?pesan=no_hp_ada");
            exit;
        }
    }

    // Insert new user
    $insert_query = "INSERT INTO users (nama_lengkap, email, no_hp, password, role_id, status_akun) VALUES ('$nama_lengkap', '$email', '$no_hp', '$password', 2, 'Pending')";
    if (mysqli_query($conn, $insert_query)) {
        header("Location: login.php?pesan=berhasil_daftar");
        exit;
    } else {
        header("Location: register.php?pesan=gagal");
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

<body>
    <div class="register-container">
        <div class="register-card p-4">
            <div class="text-center mb-4">
                <a href="index.php" class="text-decoration-none">
                    <h3 class="fw-bold text-primary"><i class="fas fa-university me-2"></i>BANK SISMUL</h3>
                </a>
                <p class="text-muted">Buat akun nasabah baru</p>
            </div>

            <?php if (isset($_GET['pesan'])) { ?>
                <div class="alert alert-danger" role="alert">
                    <?php
                    $pesan = $_GET['pesan'];
                    if ($pesan == 'gagal') {
                        echo "Pendaftaran gagal. Pastikan semua data diisi dengan benar dan password sesuai.";
                    } elseif ($pesan == 'email_ada') {
                        echo "Email sudah terdaftar. Silakan gunakan email lain.";
                    } elseif ($pesan == 'no_hp_ada') {
                        echo "No. HP sudah terdaftar. Silakan gunakan nomor lain.";
                    }
                    ?>
                </div>
            <?php } ?>

            <form action="" method="POST">
                <div class="mb-3">
                    <label class="form-label">Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" class="form-control" required placeholder="Masukkan nama sesuai KTP">
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required placeholder="name@example.com">
                </div>
                <div class="mb-3">
                    <label class="form-label">No. HP</label>
                    <input type="text" name="no_hp" class="form-control" required placeholder="0812xxxx">
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required placeholder="Minimal 8 karakter">
                </div>
                <div class="mb-3">
                    <label class="form-label">Konfirmasi Password</label>
                    <input type="password" name="konfirmasi_password" class="form-control" required placeholder="Ulangi password">
                </div>
                <button type="submit" name="register_submit" class="btn btn-primary w-100 mb-3">Daftar Sekarang</button>
                <div class="text-center">
                    <p class="mb-0 text-muted">Sudah punya akun? <a href="login.php" class="text-primary text-decoration-none fw-bold">Masuk</a></p>
                </div>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>