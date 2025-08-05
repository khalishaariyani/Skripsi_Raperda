<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once ROOT_PATH . '/session_start.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.php?msg=unauthorized");
    exit;
}

$id_undangan = intval($_GET['id'] ?? 0);
if (!$id_undangan) {
    header("Location: undangan.php?msg=invalid");
    exit;
}

$pageTitle = 'Pilih Penerima Undangan';

$undangan = $conn->query("SELECT u.*, j.judul_rapat FROM undanganrapat u JOIN jadwalrapat j ON u.idRapat = j.id WHERE u.id = $id_undangan")->fetch_assoc();

$anggota = $conn->query("SELECT nama, email FROM user WHERE role = 'anggota' AND email IS NOT NULL");
$instansi = $conn->query("SELECT nama, email FROM anggotadinas WHERE email IS NOT NULL");
?>

<!DOCTYPE html>
<html lang="en" dir="ltr" data-nav-layout="vertical" data-theme-mode="light" data-header-styles="light" data-menu-styles="dark" data-toggled="close">
<?php require_once LAYOUT_PATH . '/head.php'; ?>

<head>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .scroll-box {
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #ccc;
            padding: 10px;
            border-radius: 5px;
        }

        label {
            display: block;
            margin-bottom: 4px;
        }

        .search-input {
            margin-bottom: 8px;
            width: 100%;
            padding: 6px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .badge-area span {
            margin: 3px;
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
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Kirim Undangan: <?= htmlspecialchars($undangan['judul_rapat']) ?></h5>
                    </div>
                    <form id="undangForm" method="POST" class="card-body" action="konfirmasi_undangan.php">
                        <input type="hidden" name="id_undangan" value="<?= $id_undangan ?>">

                        <div class="row">
                            <!-- Anggota DPRD -->
                            <div class="col-md-6">
                                <h6>Anggota DPRD</h6>
                                <input type="text" class="search-input" placeholder="Cari anggota..." onkeyup="filterList(this, 'anggota-box')">
                                <div class="scroll-box" id="anggota-box">
                                    <?php while ($a = $anggota->fetch_assoc()): ?>
                                        <label>
                                            <input type="checkbox" name="penerima[]" value="<?= htmlspecialchars($a['nama']) . '|' . htmlspecialchars($a['email']) ?>" class="penerima-checkbox">
                                            <?= htmlspecialchars($a['nama']) ?> <small>&lt;<?= htmlspecialchars($a['email']) ?>&gt;</small>
                                        </label>
                                    <?php endwhile; ?>
                                </div>
                            </div>

                            <!-- Instansi Terkait -->
                            <div class="col-md-6">
                                <h6>Instansi Terkait</h6>
                                <input type="text" class="search-input" placeholder="Cari instansi..." onkeyup="filterList(this, 'instansi-box')">
                                <div class="scroll-box" id="instansi-box">
                                    <?php while ($i = $instansi->fetch_assoc()): ?>
                                        <label>
                                            <input type="checkbox" name="penerima[]" value="<?= htmlspecialchars($i['nama']) . '|' . htmlspecialchars($i['email']) ?>" class="penerima-checkbox">
                                            <?= htmlspecialchars($i['nama']) ?> <small>&lt;<?= htmlspecialchars($i['email']) ?>&gt;</small>
                                        </label>
                                    <?php endwhile; ?>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3">
                            <strong>Terpilih:</strong>
                            <div class="badge-area" id="badge-area"></div>
                        </div>

                        <div class="mt-4 d-flex justify-content-between align-items-center">
                            <a href="undangan.php" class="btn btn-secondary">Kembali</a>
                            <button type="submit" class="btn btn-success">
                                <i class="fe fe-eye"></i> Lihat & Konfirmasi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <?php require_once LAYOUT_PATH . '/scripts.php'; ?>
    <script>
        function filterList(input, targetId) {
            const filter = input.value.toLowerCase();
            const container = document.getElementById(targetId);
            const labels = container.querySelectorAll('label');

            labels.forEach(label => {
                const text = label.textContent.toLowerCase();
                label.style.display = text.includes(filter) ? '' : 'none';
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            const checkboxes = document.querySelectorAll('.penerima-checkbox');
            const badgeArea = document.getElementById('badge-area');

            checkboxes.forEach(cb => {
                cb.addEventListener('change', () => {
                    const selected = Array.from(document.querySelectorAll('.penerima-checkbox:checked')).map(c => c.value.split('|')[0]);
                    badgeArea.innerHTML = selected.map(nama => `<span class="badge bg-primary">${nama}</span>`).join(' ');
                });
            });
        });
    </script>
</body>

</html>