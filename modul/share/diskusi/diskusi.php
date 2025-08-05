<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once ROOT_PATH . '/session_start.php';

// Role yang diizinkan
if (!in_array($_SESSION['role'], ['admin', 'anggota', 'persidangan'])) {
    header("Location: ../index.php?msg=unauthorized");
    exit;
}

$pageTitle = 'Diskusi Perda';
$rapatList = $conn->query("SELECT id, judul_rapat FROM jadwalrapat WHERE status = 'disetujui' ORDER BY tanggal DESC");

$role = $_SESSION['role'];
$idLogin = $_SESSION['id'];

// Proses kirim komentar inline
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idRapat = intval($_POST['idRapat']);
    $isiKomentar = trim($_POST['isiKomentar']);
    if (!empty($idRapat) && !empty($isiKomentar)) {
        $stmt = $conn->prepare("INSERT INTO diskusiperda (idRapat, idPengguna, isiKomentar) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $idRapat, $idLogin, $isiKomentar);
        $stmt->execute();
        $stmt->close();
        header("Location: diskusi.php?msg=added");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<?php require_once LAYOUT_PATH . '/head.php'; ?>

<style>
    /* Bubble Chat Style */
    .diskusi-bubble {
        border-radius: 12px;
        padding: 12px 16px;
        position: relative;
        margin-bottom: 10px;
    }

    .diskusi-bubble .nama {
        font-weight: bold;
    }

    .diskusi-bubble .waktu {
        font-size: 0.8rem;
        color: #888;
    }

    .diskusi-bubble .isi {
        margin-top: 5px;
        white-space: pre-wrap;
    }

    .diskusi-bubble .badge-role {
        font-size: 0.75rem;
        margin-left: 6px;
        vertical-align: middle;
        border-radius: 8px;
        padding: 2px 8px;
    }

    .bubble-admin {
        background-color: #ede9fe;
    }

    .bubble-persidangan {
        background-color: #fef9c3;
    }

    .bubble-anggota {
        background-color: #ecfdf5;
    }

    .badge-admin {
        background-color: #6c5ce7;
        color: #fff;
    }

    .badge-persidangan {
        background-color: #facc15;
        color: #000;
    }

    .badge-anggota {
        background-color: #22c55e;
        color: #fff;
    }

    .avatar {
        width: 40px;
        height: 40px;
        font-weight: bold;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        color: #fff;
    }

    .avatar-admin {
        background-color: #6c5ce7;
    }

    .avatar-persidangan {
        background-color: #facc15;
        color: #000;
    }

    .avatar-anggota {
        background-color: #22c55e;
    }
</style>

<body>
    <div class="page d-flex flex-column min-vh-100">
        <?php require_once LAYOUT_PATH . '/header.php'; ?>
        <?php require_once LAYOUT_PATH . '/sidebar.php'; ?>

        <main class="main-content app-content">
            <div class="container-fluid py-4">

                <!-- Judul -->
                <div class="mb-3">
                    <h4 class="fw-bold mb-1"><?= htmlspecialchars($pageTitle) ?></h4>
                </div>

                <!-- Tombol Tambah + Search -->
                <div class="d-flex justify-content-end align-items-center gap-2 mb-4">
                    <input type="text" id="searchBox" class="form-control rounded-pill shadow-sm" style="width: 250px;" placeholder="🔍 Cari Diskusi...">
                </div>

                <!-- Loop Rapat -->
                <?php while ($rapat = $rapatList->fetch_assoc()): ?>
                    <div class="card mb-4 shadow-sm border-0">
                        <div class="card-header bg-light fw-bold"><?= htmlspecialchars($rapat['judul_rapat']) ?></div>
                        <div class="card-body">

                            <?php
                            $idRapat = $rapat['id'];
                            $komentar = $conn->query("
                                SELECT d.*, u.nama, u.role
                                FROM diskusiperda d 
                                JOIN user u ON u.id = d.idPengguna 
                                WHERE d.idRapat = $idRapat 
                                ORDER BY d.tanggalKomentar ASC
                            ");

                            if ($komentar->num_rows > 0):
                                while ($row = $komentar->fetch_assoc()):
                                    $bubbleClass = 'bubble-' . $row['role'];
                                    $avatarClass = 'avatar-' . $row['role'];
                                    $inisial = strtoupper(substr($row['nama'], 0, 1));
                            ?>
                                    <div class="d-flex align-items-start gap-2 mb-2">
                                        <div class="avatar <?= $avatarClass ?>"><?= $inisial ?></div>
                                        <div class="flex-grow-1 diskusi-bubble <?= $bubbleClass ?>">
                                            <div class="d-flex justify-content-between">
                                                <div>
                                                    <span class="nama"><?= htmlspecialchars($row['nama']) ?></span>
                                                    <?php if ($row['role'] === 'admin'): ?>
                                                        <span class="badge-role badge-admin">Admin</span>
                                                    <?php elseif ($row['role'] === 'persidangan'): ?>
                                                        <span class="badge-role badge-persidangan">Persidangan</span>
                                                    <?php elseif ($row['role'] === 'anggota'): ?>
                                                        <span class="badge-role badge-anggota">Anggota</span>
                                                    <?php endif; ?>
                                                </div>
                                                <small class="waktu"><?= date("d M Y, H:i", strtotime($row['tanggalKomentar'])) ?></small>
                                            </div>
                                            <div class="isi"><?= nl2br(htmlspecialchars($row['isiKomentar'])) ?></div>
                                            <div class="mt-2 d-flex gap-1">
                                                <?php
                                                if ($role === 'admin' || ($role === 'anggota' && $idLogin == $row['idPengguna'])): ?>
                                                    <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-warning rounded-circle shadow-sm" title="Edit">
                                                        <i class="fe fe-edit"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <?php if ($role === 'admin'): ?>
                                                    <a href="javascript:void(0)" onclick="confirmDelete('delete.php?id=<?= $row['id'] ?>')"
                                                    class="btn btn-sm btn-outline-danger rounded-circle shadow-sm" title="Hapus">
                                                        <i class="fe fe-trash"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile;
                            else: ?>
                                <p class="text-muted mb-0">Belum ada komentar.</p>
                            <?php endif; ?>

                            <!-- Form Inline -->
                            <form method="POST" class="mt-3">
                                <input type="hidden" name="idRapat" value="<?= $rapat['id'] ?>">
                                <div class="mb-2">
                                    <textarea name="isiKomentar" rows="2" class="form-control" placeholder="Tulis komentar..."></textarea>
                                </div>
                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-ungu">
                                        <i class="fe fe-send me-1"></i> Kirim Komentar
                                    </button>
                                </div>
                            </form>

                        </div>
                    </div>
                <?php endwhile; ?>

            </div>
        </main>
    </div>

    <?php require_once LAYOUT_PATH . '/scripts.php'; ?>

    <script>
        const searchInput = document.getElementById('searchBox');
        searchInput.addEventListener('input', function() {
            const val = this.value.toLowerCase();
            document.querySelectorAll('.card').forEach(card => {
                card.style.display = card.innerText.toLowerCase().includes(val) ? '' : 'none';
            });
        });
    </script>

    <style>
        .btn-ungu {
            background-color: #6c5ce7;
            color: #fff;
            border: none;
        }

        .btn-ungu:hover {
            background-color: #5a4bcf;
        }
    </style>
</body>

</html>