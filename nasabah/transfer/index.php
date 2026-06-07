<?php
session_start();
include "../../config/database.php";
// Contoh penggunaan: $result = mysqli_query($conn, "SELECT * FROM table_name");

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
    <title>Transfer Dana</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .hero {
            background: linear-gradient(135deg, #4f46e5, #312e81);
            color: white;
        }
    </style>
</head>

<body class="d-flex flex-column min-vh-100">

    <!-- Navbar -->
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
                        <a class="nav-link active"
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

    <!-- Header -->
    <header class="hero py-5 shadow-sm">
        <div class="container">
            <h1 class="display-6 fw-bold">
                Transfer Dana
            </h1>
            <p class="mb-0 opacity-75">
                Kirim uang ke sesama nasabah Bank Multimedia.
            </p>
        </div>
    </header>

    <main class="flex-grow-1">
        <div class="container-fluid py-4">

            <!-- Workspace -->

        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-light border-top py-3">
        <div class="container-fluid text-center">
            <small>
                © <?= date('Y'); ?> Bank Multimedia
            </small>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>