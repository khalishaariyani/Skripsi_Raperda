<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once ROOT_PATH . '/session_start.php';
require_once CONFIG_PATH . '/koneksi.php';


$id = $_SESSION['id'];
$role = $_SESSION['role'];

$errors = [];

// Ambil data user
$stmt = $conn->prepare("SELECT * FROM user WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Proses update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password_baru = $_POST['password'] ?? '';
    $foto = $user['foto']; // default = existing

    if ($nama === '' || $email === '') {
        $errors[] = 'Nama lengkap dan email wajib diisi.';
    }

    // Upload foto baru jika ada
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $nama_baru = 'user_' . $id . '_' . time() . '.' . $ext;
        $tujuan = ROOT_PATH . '/uploads/' . $nama_baru;

        if (move_uploaded_file($_FILES['foto']['tmp_name'], $tujuan)) {
            $foto = $nama_baru;
        } else {
            $errors[] = 'Gagal mengunggah foto.';
        }
    }

    if (empty($errors)) {
        if ($password_baru !== '') {
            // ⛔ TANPA hash
            $stmt = $conn->prepare("UPDATE user SET nama=?, email=?, password=?, foto=? WHERE id=?");
            $stmt->bind_param("ssssi", $nama, $email, $password_baru, $foto, $id);
        } else {
            $stmt = $conn->prepare("UPDATE user SET nama=?, email=?, foto=? WHERE id=?");
            $stmt->bind_param("sssi", $nama, $email, $foto, $id);
        }

        if ($stmt->execute()) {
            $_SESSION['nama'] = $nama;
            $_SESSION['email'] = $email;
            $_SESSION['foto'] = $foto;

            header("Location: profil.php?updated=1");
            exit;
        } else {
            $errors[] = 'Gagal memperbarui profil.';
        }
    }
}

?>

<!-- Include Layout -->
<?php
require_once LAYOUT_PATH . '/head.php';
require_once LAYOUT_PATH . '/header.php';
require_once LAYOUT_PATH . '/sidebar.php';

$foto = $user['foto'] ? BASE_URL . '/uploads/' . $user['foto'] : ASSETS_URL . '/images/default-avatar.png';
?>


<div class="main-content app-content">
    <div class="container-fluid">
        <div class="text-center mb-4 pt-3">
            <h4><i class="fe fe-user me-2 text-primary"></i>Profil Saya</h4>
            <p class="text-muted">Perbarui data akun Anda secara mandiri.</p>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fe fe-alert-circle me-1"></i> <?= implode('<br>', $errors) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php elseif (isset($_GET['updated'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fe fe-check-circle me-1"></i> Profil berhasil diperbarui.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row justify-content-center mt-2">
            <div class="col-xl-6 col-lg-7 col-md-10">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <!-- Foto -->
                            <div class="text-center mb-4 position-relative">
                                <img src="<?= $foto ?>" class="rounded-circle shadow border" width="100" height="100" alt="Foto Profil">
                                <label for="foto" class="position-absolute bottom-0 end-0 translate-middle-x bg-white border rounded-circle p-1 shadow-sm" style="cursor: pointer;">
                                    <i class="fe fe-camera text-primary"></i>
                                </label>
                            </div>

                            <!-- Nama Lengkap -->
                            <div class="form-floating mb-3">
                                <input type="text" name="nama" class="form-control" id="nama" value="<?= htmlspecialchars($user['nama']) ?>" required placeholder="Nama Lengkap">
                                <label for="nama">Nama Lengkap</label>
                            </div>

                            <!-- Email -->
                            <div class="form-floating mb-3">
                                <input type="email" name="email" class="form-control" id="email" value="<?= htmlspecialchars($user['email']) ?>" required placeholder="Email">
                                <label for="email">Email</label>
                            </div>

                            <!-- Password -->
                            <div class="form-floating mb-3 position-relative">
                                <input type="password" name="password" class="form-control" id="password" placeholder="Password baru">
                                <label for="password">Ganti Password (opsional)</label>
                                <span onclick="togglePassword('password')" class="position-absolute top-50 end-0 translate-middle-y me-3" style="cursor:pointer;">
                                    <i id="eye-icon" class="fe fe-eye-off text-muted"></i>
                                </span>
                            </div>

                            <!-- Upload Foto -->
                            <div class="mb-3">
                                <label for="foto" class="form-label">Ganti Foto Profil</label>
                                <input type="file" name="foto" class="form-control" id="foto" accept="image/*" onchange="previewFoto(this)">
                                <img id="preview-img" src="<?= $foto ?>" class="rounded shadow mt-2 border" width="80" height="80" alt="Preview Foto">
                            </div>

                            <!-- Buttons -->
                            <div class="d-flex justify-content-between mt-4">
                                <div class="d-flex justify-content-end mb-3">
                                    <button type="button" onclick="window.location.href='<?= BASE_URL ?>/modul/<?= $role ?>/dashboard.php'" class="btn btn-secondary">
                                        <i class="fe fe-arrow-left me-1"></i> Kembali ke Dashboard
                                    </button>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fe fe-save me-1"></i> Simpan Perubahan
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once LAYOUT_PATH . '/footer.php';
require_once LAYOUT_PATH . '/scripts.php';
?>

<script>
    function previewFoto(input) {
        const file = input.files[0];
        if (!file) return;

        const allowed = ['image/jpeg', 'image/png', 'image/jpg'];
        if (!allowed.includes(file.type)) {
            alert("Format harus .jpg, .jpeg atau .png");
            input.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview-img').src = e.target.result;
        };
        reader.readAsDataURL(file);
    }

    function togglePassword(id) {
        const input = document.getElementById(id);
        const icon = document.getElementById('eye-icon');
        if (input.type === "password") {
            input.type = "text";
            icon.classList.replace('fe-eye-off', 'fe-eye');
        } else {
            input.type = "password";
            icon.classList.replace('fe-eye', 'fe-eye-off');
        }
    }
</script>