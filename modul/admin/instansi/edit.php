<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once ROOT_PATH . '/session_start.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.php?msg=unauthorized");
    exit;
}

$id = intval($_GET['id'] ?? 0);
$data = [];

if ($id > 0) {
    $stmt = $conn->prepare("SELECT * FROM anggotadinas WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama']);
    $jabatan = trim($_POST['jabatan']);
    $email = trim($_POST['email']);
    $telepon = trim($_POST['telepon']);

    if ($nama && $jabatan && $email) {
        $stmt = $conn->prepare("UPDATE anggotadinas SET nama = ?, jabatan = ?, email = ?, telepon = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $nama, $jabatan, $email, $telepon, $id);

        if ($stmt->execute()) {
            header("Location: instansi.php?msg=updated&obj=anggotadinas");
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


<!DOCTYPE html>
<html lang="id">

<head>
    <?php require_once LAYOUT_PATH . '/head.php'; ?>
    <style>
        /* Tombol Ungu Custom */
        .btn-ungu {
            background-color: #6c5ce7;
            /* Sesuaikan dengan warna di foto Icha cantik */
            color: #fff;
            border: none;
        }

        .btn-ungu:hover {
            background-color: #5a4bcf;
            /* Warna hover lebih gelap */
        }
    </style>
</head>

<body>
    <div class="page">
        <?php require_once LAYOUT_PATH . '/switcher.php'; ?>
        <?php require_once LAYOUT_PATH . '/header.php'; ?>
        <?php require_once LAYOUT_PATH . '/sidebar.php'; ?>

        <main class="main-content app-content mt-0">
            <div class="container-fluid mt-4">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Edit Data Anggota Dinas</h5>
                    </div>
                    <form method="POST" class="card-body">
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <label class="form-label">Nama</label>
                            <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($data['nama'] ?? '') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Instansi</label>
                            <input type="text" name="jabatan" class="form-control" value="<?= htmlspecialchars($data['jabatan'] ?? '') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($data['email'] ?? '') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Telepon</label>
                            <input type="text" name="telepon" class="form-control" value="<?= htmlspecialchars($data['telepon'] ?? '') ?>">
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="instansi.php" class="btn btn-secondary">
                                <i class="fa fa-arrow-left"></i> Kembali
                            </a>
                            <button type="submit" class="btn btn-ungu">
                                <i class="fa fa-save"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
    <?php require_once LAYOUT_PATH . '/scripts.php'; ?>
</body>

</html>