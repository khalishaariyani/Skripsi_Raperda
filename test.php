<?php


$pageTitle = 'Dashboard';
$role = $_SESSION['role'] ?? 'guest';
$nama  = $_SESSION['nama'] ?? 'User';
?>

<!DOCTYPE html>
<html lang="en" dir="ltr" data-nav-layout="vertical" data-theme-mode="light" data-header-styles="light" data-menu-styles="dark" data-toggled="close">

<?php require_once 'head.php'; ?>

<body class="d-flex flex-column min-vh-100">
    <div class="page d-flex flex-column flex-grow-1">
        <?php require_once 'switcher.php'; ?>
        <?php require_once 'header.php'; ?>
        <?php require_once 'sidebar.php'; ?>

        <main class="main-content app-content flex-grow-1">
            <div class="container-fluid">
                <!-- Greeting -->
                <div class="mb-0">
                    <h3 class="fw-bold">Halo, <?= $nama ?> 👋</h3>
                    <p class="text-muted">Selamat datang di Sistem Raperda — Anda login sebagai <strong><?= ucfirst($role) ?></strong>.</p>
                </div>

                <!-- Statistik Dummy (bisa diganti dinamis nanti) -->
                <div class="row">
                    <div class="col-md-4 col-sm-6">
                        <div class="card text-white bg-primary">
                            <div class="card-body">
                                <h5 class="card-title">Jumlah Rapat</h5>
                                <p class="card-text fs-4">12</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-6">
                        <div class="card text-white bg-success">
                            <div class="card-body">
                                <h5 class="card-title">Notulen Baru</h5>
                                <p class="card-text fs-4">5</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-6">
                        <div class="card text-white bg-info">
                            <div class="card-body">
                                <h5 class="card-title">Dokumen Masuk</h5>
                                <p class="card-text fs-4">8</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
        <?php require_once 'footer.php'; ?>
    </div>
    <?php require_once 'scripts.php'; ?>
</body>

</html>