<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';

    if (!$nama || !$email || !$password || !$role) {
        header("Location: register.php?msg=kosong&obj=register");
        exit;
    }

    $cek = $conn->prepare("SELECT id FROM user WHERE email = ?");
    $cek->bind_param("s", $email);
    $cek->execute();
    $cek->store_result();

    if ($cek->num_rows > 0) {
        header("Location: register.php?msg=email_used&obj=register");
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO user (nama, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nama, $email, $password, $role);

    if ($stmt->execute()) {
        $_SESSION['id'] = $stmt->insert_id;
        $_SESSION['role'] = $role;
        $_SESSION['nama'] = $nama;

        header("Location: register.php?msg=success&obj=register");
        exit;
    } else {
        header("Location: register.php?msg=error&obj=register");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Daftar Akun - RAPERDA</title>
    <link rel="icon" href="assets/images/brand-logos/logoatas2.png" type="image/png">
    <link href="assets/libs/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/styles.min.css" rel="stylesheet">
    <link href="assets/css/icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="error-1">
    <div class="page main-signin-wrapper">
        <div class="row signpages text-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="row row-sm">
                        <!-- Sisi Kiri -->
                        <div class="col-lg-6 col-xl-5 d-none d-lg-block text-center bg-primary details">
                            <div class="mt-5 pt-5 p-2 position-absolute">
                                <img src="assets/images/brand-logos/logodpr.png" class="header-brand-img mb-2" alt="logo">
                                <span class="text-white-6 fs-13 mb-2 mt-xl-0">Daftarkan diri anda untuk mengakses sistem RAPERDA</span>
                            </div>
                        </div>

                        <!-- Form -->
                        <div class="col-lg-6 col-xl-7 col-xs-12 col-sm-12 login_form">
                            <div class="main-container container-fluid">
                                <div class="row row-sm">
                                    <div class="card-body mt-2 mb-2">
                                        <h5 class="text-start mb-2">Daftar Akun Baru</h5>

                                        <form method="POST">
                                            <div class="form-group text-start">
                                                <label>Nama Lengkap</label>
                                                <input name="nama" class="form-control" placeholder="Masukkan Nama" required>
                                            </div>

                                            <div class="form-group text-start mt-3">
                                                <label>Email</label>
                                                <input name="email" type="email" class="form-control" placeholder="Masukkan Email" required>
                                            </div>

                                            <div class="form-group text-start mt-3">
                                                <label>Password Baru</label>
                                                <div class="input-group">
                                                    <input id="password" name="password" type="password" class="form-control" placeholder="Masukkan Password" required>
                                                    <span class="input-group-text" onclick="togglePassword()" style="cursor:pointer;">
                                                        <i class="bi bi-eye" id="toggleIcon"></i>
                                                    </span>
                                                </div>
                                            </div>

                                            <div class="form-group text-start mt-3">
                                                <label>Pilih Role</label>
                                                <select name="role" class="form-control" required>
                                                    <option value="">-- Pilih Role --</option>
                                                    <option value="anggota">Anggota</option>
                                                    <option value="persidangan">Persidangan</option>
                                                </select>
                                            </div>

                                            <div class="form-group mt-4 d-grid">
                                                <button type="submit" class="btn btn-primary">Buat Akun</button>
                                            </div>
                                        </form>

                                        <div class="text-start mt-4">
                                            <p>Sudah punya akun? <a href="index.php">Masuk di sini</a></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div> <!-- Form col -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap -->
    <script src="assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Toggle Password Script -->
    <script>
        function togglePassword() {
            const passwordField = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleIcon.classList.remove('bi-eye');
                toggleIcon.classList.add('bi-eye-slash');
            } else {
                passwordField.type = 'password';
                toggleIcon.classList.remove('bi-eye-slash');
                toggleIcon.classList.add('bi-eye');
            }
        }
    </script>

    <!-- SweetAlert Handler -->
    <?php if (isset($_GET['msg']) && $_GET['obj'] === 'register'): ?>
        <script>
            const msg = "<?= $_GET['msg'] ?>";
            const alertMap = {
                kosong: {
                    icon: "warning",
                    title: "Form Tidak Lengkap",
                    text: "Semua field wajib diisi."
                },
                email_used: {
                    icon: "error",
                    title: "Email Sudah Digunakan",
                    text: "Silakan gunakan email lain."
                },
                error: {
                    icon: "error",
                    title: "Gagal Mendaftar",
                    text: "Terjadi kesalahan saat menyimpan data."
                },
                success: {
                    icon: "success",
                    title: "Berhasil Mendaftar",
                    text: "Akun Anda berhasil dibuat. Silakan login."
                }
            };

            if (alertMap[msg]) {
                Swal.fire({
                    icon: alertMap[msg].icon,
                    title: alertMap[msg].title,
                    text: alertMap[msg].text,
                    confirmButtonColor: '#3085d6',
                    timer: 3000,
                    timerProgressBar: true
                }).then(() => {
                    if (msg === 'success') {
                        window.location.href = 'index.php';
                    }
                });

                if (window.history.replaceState) {
                    const url = new URL(window.location);
                    url.searchParams.delete('msg');
                    url.searchParams.delete('obj');
                    window.history.replaceState({}, '', url);
                }
            }
        </script>
    <?php endif; ?>
</body>

</html>