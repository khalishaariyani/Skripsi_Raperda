<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once ROOT_PATH . '/session_start.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.php?msg=unauthorized");
    exit;
}

$pageTitle = 'Tambah Anggota Dinas';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama']);
    $jabatan = trim($_POST['jabatan']);
    $email = trim($_POST['email']);
    $telepon = trim($_POST['telepon']);

    if ($nama && $jabatan && $email) {
        $stmt = $conn->prepare("INSERT INTO anggotadinas (nama, jabatan, email, telepon) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $nama, $jabatan, $email, $telepon);

        if ($stmt->execute()) {
            header("Location: instansi.php?msg=added&obj=anggotadinas");
            exit;
        } else {
            header("Location: instansi.php?msg=error&obj=anggotadinas");
            exit;
        }
    } else {
        $error = "Nama, Jabatan, dan Email wajib diisi.";
    }
}
?>

<!DOCTYPE html>
<html lang="en" dir="ltr" data-nav-layout="vertical" data-theme-mode="light" data-header-styles="light" data-menu-styles="dark" data-toggled="close">
<?php require_once LAYOUT_PATH . '/head.php'; ?>

<body>
    <div class="page">
        <?php require_once LAYOUT_PATH . '/switcher.php'; ?>
        <?php require_once LAYOUT_PATH . '/header.php'; ?>
        <?php require_once LAYOUT_PATH . '/sidebar.php'; ?>

        <main class="main-content app-content mt-0">
            <div class="card custom-card">
                <div class="px-4 pt-4 pb-2" style="background: #eceff6; border-top-left-radius: .5rem; border-top-right-radius: .5rem;">
                    <h2 class="mb-0" style="font-size:1.3rem; font-weight:700; color:#222; line-height:1;">
                        Tambah Anggota Dinas
                    </h2>
                </div>
                <form method="POST" class="card-body">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label">Nama</label>
                        <input type="text" name="nama" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Asal Instansi</label>
                        <input type="text" name="jabatan" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Telepon</label>
                        <input type="text" name="telepon" class="form-control">
                    </div>

                    <div class="d-flex justify-content-end">
                        <a href="instansi.php" class="btn btn-secondary me-2">Batal</a>
                        <button type="submit" class="btn btn-primary"><i class="fe fe-save me-1"></i> Simpan</button>
                    </div>
                </form>
            </div>
    </div>
    </main>
    </div>
    <?php require_once LAYOUT_PATH . '/scripts.php'; ?>
</body>

</html>