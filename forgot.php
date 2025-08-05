<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Cek nama pengguna
    $stmt = $conn->prepare("SELECT * FROM user WHERE nama = ?");
    $stmt->bind_param("s", $nama);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        header("Location: forgot.php?msg=nama_not_found&obj=forgot");
        exit;
    }

    // Cek apakah email cocok dengan user tersebut
    if ($user['email'] !== $email) {
        header("Location: forgot.php?msg=email_wrong&obj=forgot");
        exit;
    }

    // Jika valid, update password
    $stmt = $conn->prepare("UPDATE user SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $password, $user['id']);
    $stmt->execute();

    header("Location: forgot.php?msg=updated&obj=forgot");
    exit;
}
?>


<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Reset Password - RAPERDA</title>
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
                <div class="card mb-0">
                    <div class="row row-sm">

                        <!-- Kolom Kiri -->
                        <div class="col-lg-6 col-xl-4 d-none d-lg-block text-center bg-primary details">
                            <div class="mt-5 pt-5 p-2 position-absolute">
                                <a href="index.php">
                                    <img src="assets/images/brand-logos/logodpr.png" class="header-brand-img mb-2" alt="logo">
                                </a>
                                <h5 class="mt-4 text-white">Reset Password</h5>
                            </div>
                        </div>

                        <!-- Form -->
                        <div class="col-lg-6 col-xl-7 col-xs-12 col-sm-12 login_form">
                            <div class="main-container container-fluid">
                                <div class="row row-sm">
                                    <div class="card-body mt-2 mb-2">

                                        <form method="POST">
                                            <h5 class="text-center mb-3 fw-bold">RESET PASSWORD</h5>

                                            <div class="form-group text-start mb-3">
                                                <label class="form-label">Nama Pengguna</label>
                                                <div class="input-group">
                                                    <input class="form-control" name="nama" placeholder="Nama Pengguna" required>
                                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                                </div>
                                            </div>

                                            <div class="form-group text-start mb-3">
                                                <label class="form-label">Email</label>
                                                <div class="input-group">
                                                    <input class="form-control" name="email" type="email" placeholder="Email" required>
                                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                                </div>
                                            </div>

                                            <div class="form-group text-start mb-3">
                                                <label class="form-label">Password Baru</label>
                                                <div class="input-group">
                                                    <input class="form-control" id="password" name="password" type="password" placeholder="Password Baru" required>
                                                    <span class="input-group-text" onclick="togglePassword()" style="cursor:pointer;">
                                                        <i class="bi bi-eye" id="toggleIcon"></i>
                                                    </span>
                                                </div>
                                            </div>

                                            <div class="d-grid mt-4">
                                                <button type="submit" class="btn btn-primary">Reset</button>
                                            </div>
                                        </form>

                                        <div class="text-center mt-4">
                                            <a href="index.php">Kembali ke Login</a>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div> <!-- end form -->

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle -->
    <script src="assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Toggle Password -->
    <script>
        function togglePassword() {
            const field = document.getElementById('password');
            const icon = document.getElementById('toggleIcon');
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        }
    </script>

    <!-- SweetAlert Handler -->
    <?php if (isset($_GET['msg']) && $_GET['obj'] === 'forgot'): ?>
        <script>
            const msg = "<?= $_GET['msg'] ?>";
            const alerts = {
                updated: {
                    icon: "success",
                    title: "Berhasil",
                    text: "Password berhasil direset. Silakan login kembali."
                },
                nama_not_found: {
                    icon: "error",
                    title: "Nama Tidak Ditemukan",
                    text: "Nama pengguna tidak terdaftar dalam sistem."
                },
                email_wrong: {
                    icon: "error",
                    title: "Email Tidak Cocok",
                    text: "Email tidak sesuai dengan nama pengguna."
                }
            };

            if (alerts[msg]) {
                Swal.fire({
                    icon: alerts[msg].icon,
                    title: alerts[msg].title,
                    text: alerts[msg].text,
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