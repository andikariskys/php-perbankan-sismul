<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php?pesan=belum_login');
    exit;
}

if ($_SESSION['nama_role'] !== 'Admin') {
    header('Location: /login.php?pesan=akses_ditolak');
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Nasabah</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .hero {
            background: linear-gradient(135deg, #6f42c1, #432874);
            color: white;
        }
    </style>
</head>

<body class="d-flex flex-column min-vh-100">

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">

            <a class="navbar-brand fw-bold" href="#">
                Bank Multimedia
            </a>

            <button class="navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navbarAdmin">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarAdmin">

                <ul class="navbar-nav me-auto">

                    <li class="nav-item">
                        <a class="nav-link"
                            href="../dashboard/index.php">
                            Dashboard
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link active"
                            href="../nasabah/index.php">
                            Nasabah
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
                            href="../aktivitas/index.php">
                            Aktivitas
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link"
                            href="../logs/index.php">
                            Logs
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

    <!-- HEADER -->
    <header class="hero py-5 shadow-sm">
        <div class="container">

            <h1 class="display-6 fw-bold">
                Kelola Nasabah
            </h1>

            <p class="mb-0 opacity-75">
                Tambah, edit, dan hapus data nasabah Bank Multimedia.
            </p>

        </div>
    </header>

    <!-- Workspace -->
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