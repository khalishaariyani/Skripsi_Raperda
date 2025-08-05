<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once ROOT_PATH . '/session_start.php';
require_once CONFIG_PATH . '/koneksi.php';

// ✅ Proteksi Akses
if ($_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "/user.php?msg=unauthorized&obj=user");
    exit;
}

// ✅ Ambil ID dari URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    header("Location: user.php?msg=invalid&obj=user");
    exit;
}

// ✅ Ambil data user
$query = $conn->query("SELECT * FROM user WHERE id = $id");
if ($query->num_rows === 0) {
    header("Location: user.php?msg=notfound&obj=user");
    exit;
}
$user = $query->fetch_assoc();

// ✅ Proses jika disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama     = trim($_POST['nama']);
    $email    = trim($_POST['email']);
    $role     = trim($_POST['role']);
    $password = trim($_POST['password'] ?? '');

    if (empty($nama) || empty($email) || empty($role)) {
        header("Location: edit.php?id=$id&msg=kosong&obj=user");
        exit;
    }

    if (!empty($password)) {
        $stmt = $conn->prepare("UPDATE user SET nama = ?, email = ?, role = ?, password = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $nama, $email, $role, $password, $id);
    } else {
        $stmt = $conn->prepare("UPDATE user SET nama = ?, email = ?, role = ? WHERE id = ?");
        $stmt->bind_param("sssi", $nama, $email, $role, $id);
    }

    if ($stmt->execute()) {
        header("Location: user.php?msg=updated&obj=user");
        exit;
    } else {
        header("Location: edit.php?id=$id&msg=error&obj=user");
        exit;
    }
}
?>


<!DOCTYPE html>
<html lang="id" dir="ltr" data-nav-layout="vertical" data-theme-mode="light" data-header-styles="light" data-menu-styles="dark" data-toggled="close">
<?php require_once LAYOUT_PATH . '/head.php'; ?>
?>

<body class="d-flex flex-column min-vh-100">
    <div class="page d-flex flex-column flex-grow-1">
        <?php require_once LAYOUT_PATH . '/switcher.php'; ?>
        <?php require_once LAYOUT_PATH . '/header.php'; ?>
        <?php require_once LAYOUT_PATH . '/sidebar.php'; ?>

        <main class="main-content app-content flex-grow-1">
            <div class="container-fluid mt-4">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Edit Data Pengguna</h5>
                    </div>
                    <form method="post" class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($user['nama']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select name="role" class="form-select" required>
                                <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                <option value="persidangan" <?= $user['role'] === 'persidangan' ? 'selected' : '' ?>>Sekretariat</option>
                                <option value="anggota" <?= $user['role'] === 'anggota' ? 'selected' : '' ?>>Anggota Rapat</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password <small class="text-muted">(Kosongkan jika tidak ingin diubah)</small></label>
                            <div class="input-group">
                                <input type="password" name="password" class="form-control" placeholder="Masukkan password baru" id="passwordInput">
                                <span class="input-group-text" onclick="togglePassword()" style="cursor:pointer;">
                                    <i class="bi bi-eye" id="toggleIcon"></i>
                                </span>
                            </div>
                        </div>
                        <script>
                            function togglePassword() {
                                const input = document.getElementById('passwordInput');
                                const icon = document.getElementById('toggleIcon');
                                if (input.type === 'password') {
                                    input.type = 'text';
                                    icon.classList.remove('bi-eye');
                                    icon.classList.add('bi-eye-slash');
                                } else {
                                    input.type = 'password';
                                    icon.classList.remove('bi-eye-slash');
                                    icon.classList.add('bi-eye');
                                }
                            }
                        </script>
                        <div class="d-flex justify-content-between">
                            <a href="user.php" class="btn btn-secondary">Kembali</a>
                            <button type="submit" class="btn btn-primary"><i class="fe fe-save me-1"></i> Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
    <?php require_once LAYOUT_PATH . '/scripts.php'; ?>
</body>

</html>