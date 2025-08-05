<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nama = trim($_POST['nama']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT * FROM user WHERE nama = ? AND password = ?");
    $stmt->bind_param("ss", $nama, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $_SESSION['id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['nama'] = $user['nama'];
        $_SESSION['foto'] = $user['foto'];

        // Redirect berdasarkan role + tambahkan msg
        if ($user['role'] === 'admin') {
            header("Location: modul/admin/dashboard.php?msg=loginsuccess");
        } elseif ($user['role'] === 'persidangan') {
            header("Location: modul/persidangan/dashboard.php?msg=loginsuccess");
        } elseif ($user['role'] === 'anggota') {
            header("Location: modul/anggota/dashboard.php?msg=loginsuccess");
        } else {
            header("Location: index.php?msg=invalid_role&obj=login");
        }
        exit;
    } else {
        header("Location: index.php?msg=failed&obj=login");
        exit;
    }
}
?>
<!-- HTML login tetap sama -->
<!-- ... Lanjutkan dengan tampilan login dan SweetAlert login seperti sebelumnya ... -->


<!DOCTYPE html>
<html lang="id" dir="ltr" data-nav-layout="vertical" data-theme-mode="light" data-header-styles="light" data-menu-styles="dark" data-toggled="close">

<head>
    <meta charset="UTF-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0, user-scalable=no'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>RAPERDA - DPRD KOTA BANJARMASIN</title>
    <meta name="description" content="Halaman Login Aplikasi RAPERDA">
    <meta name="author" content="UNISKA">
    <meta name="keywords" content="RAPERDA, DPRD, Banjarmasin, Sistem Legislasi, Aplikasi DPRD">

    <link rel="icon" href="assets/images/brand-logos/logoatas2.png" type="image/png">
    <link id="style" href="assets/libs/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/styles.min.css" rel="stylesheet">
    <link href="assets/css/icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        if (localStorage.spruhalandingdarktheme) {
            document.querySelector("html").setAttribute("data-theme-mode", "dark")
        }
        if (localStorage.spruhalandingrtl) {
            document.querySelector("html").setAttribute("dir", "rtl")
            document.querySelector("#style")?.setAttribute("href", "assets/libs/bootstrap/css/bootstrap.rtl.min.css");
        }
    </script>
</head>

<body class="error-1">
    <div class="page main-signin-wrapper">
        <div class="row signpages text-center">
            <div class="col-md-12">
                <div class="card mb-0">
                    <div class="row row-sm">
                        <!-- Kolom Kiri -->
                        <div class="col-lg-6 col-xl-4 d-none d-lg-block text-center bg-primary details">
                            <div class="mt-5 pt-5 p-2 position-absolute">
                                <a href="index.php">
                                    <img src="assets/images/brand-logos/logodpr.png" class="header-brand-img mb-2" alt="logo">
                                </a>
                            </div>
                        </div>

                        <!-- Form Login -->
                        <div class="col-lg-6 col-xl-7 col-xs-12 col-sm-12 login_form">
                            <div class="main-container container-fluid">
                                <div class="row row-sm">
                                    <div class="card-body mt-2 mb-2">
                                        <form method="POST">
                                            <h5 class="text-center mb-2 fw-bold">Masuk ke Akun Anda</h5>

                                            <div class="form-group text-start">
                                                <label class="form-label">Nama Pengguna</label>
                                                <input class="form-control" placeholder="Masukkan nama pengguna" type="text" name="nama" required>
                                            </div>

                                            <div class="form-group text-start">
                                                <label class="form-label">Kata Sandi</label>
                                                <div class="input-group">
                                                    <input class="form-control" placeholder="Masukkan kata sandi" type="password" name="password" id="passwordInput" required>
                                                    <span class="input-group-text" onclick="togglePassword()" style="cursor:pointer;">
                                                        <i class="bi bi-eye" id="toggleIcon"></i>
                                                    </span>
                                                </div>
                                            </div>
                                            <script>
                                                function togglePassword() {
                                                    const passwordInput = document.getElementById('passwordInput');
                                                    const toggleIcon = document.getElementById('toggleIcon');
                                                    if (passwordInput.type === 'password') {
                                                        passwordInput.type = 'text';
                                                        toggleIcon.classList.remove('bi-eye');
                                                        toggleIcon.classList.add('bi-eye-slash');
                                                    } else {
                                                        passwordInput.type = 'password';
                                                        toggleIcon.classList.remove('bi-eye-slash');
                                                        toggleIcon.classList.add('bi-eye');
                                                    }
                                                }
                                            </script>

                                            <div class="d-grid mt-3">
                                                <button type="submit" class="btn btn-primary">Masuk</button>
                                            </div>
                                        </form>

                                        <div class="text-start mt-5 ms-0">
                                            <div class="mb-1"><a href="forgot.php">Lupa kata sandi?</a></div>
                                            <div>Belum punya akun? <a href="register.php">Daftar di sini</a></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div> <!-- End Form -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap & Switcher JS -->
    <script src="assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/custom-switcher.min.js"></script>

    <!-- SweetAlert Login Handler -->
    <?php if (isset($_GET['msg']) && $_GET['obj'] === 'login'): ?>
        <script>
            const msg = "<?= $_GET['msg'] ?>";
            const alertMap = {
                failed: {
                    icon: "error",
                    title: "Login Gagal",
                    text: "Nama pengguna atau password salah."
                },
                invalid_role: {
                    icon: "error",
                    title: "Role Tidak Dikenali",
                    text: "Hubungi admin untuk verifikasi akun Anda."
                }
            };

            if (alertMap[msg]) {
                Swal.fire({
                    icon: alertMap[msg].icon,
                    title: alertMap[msg].title,
                    text: alertMap[msg].text,
                    confirmButtonColor: "#3085d6"
                });

                if (window.history.replaceState) {
                    const url = new URL(window.location);
                    url.searchParams.delete("msg");
                    url.searchParams.delete("obj");
                    window.history.replaceState({}, '', url);
                }
            }
        </script>
    <?php endif; ?>
</body>

</html>