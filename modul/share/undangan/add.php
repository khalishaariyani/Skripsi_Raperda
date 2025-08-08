<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once ROOT_PATH . '/session_start.php';

// Proteksi hanya admin & persidangan boleh tambah
if (!in_array($_SESSION['role'], ['admin', 'persidangan'])) {
    header("Location: undangan.php?msg=unauthorized&obj=undangan");
    exit;
}

$pageTitle = 'Tambah Undangan Rapat';

// ✅ Ambil rapat yang TIDAK dibatalkan
$rapats = $conn->query("
    SELECT id, judul_rapat 
    FROM jadwalrapat 
    WHERE status != 'dibatalkan' 
    ORDER BY tanggal DESC
");

$anggota = $conn->query("SELECT id, nama FROM user WHERE role = 'anggota' ORDER BY nama ASC");
$instansi = $conn->query("SELECT id, nama FROM anggotadinas WHERE isAktif = 1 ORDER BY nama ASC");

// Handle submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_rapat = intval($_POST['id_rapat'] ?? 0);
    $tanggal = $_POST['tanggal'] ?? '';
    $jam = $_POST['jam'] ?? '';
    $lokasi = trim($_POST['lokasi'] ?? '');
    $catatan = trim($_POST['catatan'] ?? '');

    $penerima_anggota = $_POST['penerima_anggota'] ?? [];
    $penerima_instansi = $_POST['penerima_instansi'] ?? [];

    if (!is_array($penerima_anggota)) $penerima_anggota = [];
    if (!is_array($penerima_instansi)) $penerima_instansi = [];

    if (!$id_rapat || !$tanggal || !$jam || !$lokasi || (empty($penerima_anggota) && empty($penerima_instansi))) {
        header("Location: add.php?msg=kosong&obj=undangan");
        exit;
    }

    $berhasil = false;

    foreach ($penerima_anggota as $idAnggota) {
        $idAnggota = intval($idAnggota);
        $get = $conn->prepare("SELECT nama FROM user WHERE id = ? AND role = 'anggota'");
        $get->bind_param("i", $idAnggota);
        $get->execute();
        $res = $get->get_result();
        if ($row = $res->fetch_assoc()) {
            $penerima = $row['nama'];
            $stmt = $conn->prepare("INSERT INTO undanganrapat (idRapat, penerima, tanggal, jam, lokasi, catatan) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isssss", $id_rapat, $penerima, $tanggal, $jam, $lokasi, $catatan);
            if ($stmt->execute()) {
                $berhasil = true;
            }
        }
    }

    foreach ($penerima_instansi as $idInstansi) {
        $idInstansi = intval($idInstansi);
        $get = $conn->prepare("SELECT nama FROM anggotadinas WHERE id = ?");
        $get->bind_param("i", $idInstansi);
        $get->execute();
        $res = $get->get_result();
        if ($row = $res->fetch_assoc()) {
            $penerima = $row['nama'];
            $stmt = $conn->prepare("INSERT INTO undanganrapat (idRapat, penerima, tanggal, jam, lokasi, catatan) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isssss", $id_rapat, $penerima, $tanggal, $jam, $lokasi, $catatan);
            if ($stmt->execute()) {
                $berhasil = true;
            }
        }
    }

    if ($berhasil) {
        header("Location: undangan.php?msg=added&obj=undangan");
    } else {
        header("Location: add.php?msg=error&obj=undangan");
    }
    exit;
}

// Jika AJAX request
if (isset($_GET['ajax_jadwal'])) {
    $id = intval($_GET['ajax_jadwal']);
    $stmt = $conn->prepare("SELECT tanggal, waktu, tempat FROM jadwalrapat WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    echo json_encode($result);
    exit;
}
?>



<!DOCTYPE html>
<html lang="id">
<?php require_once LAYOUT_PATH . '/head.php'; ?>

<body class="d-flex flex-column min-vh-100">
    <div class="page d-flex flex-column flex-grow-1">
        <?php require_once LAYOUT_PATH . '/switcher.php'; ?>
        <?php require_once LAYOUT_PATH . '/header.php'; ?>
        <?php require_once LAYOUT_PATH . '/sidebar.php'; ?>

        <div class="main-content app-content mt-4">
            <div class="container-fluid">
                <div class="px-4 pt-4 pb-2" style="background: #eceff6; border-top-left-radius: .5rem; border-top-right-radius: .5rem;">
                    <h2 class="mb-0" style="font-size:1.3rem; font-weight:700; color:#222; line-height:1;">
                        Tambah Undangan Rapat
                    </h2>
                </div>
                <form method="POST" class="card-body">
                    <div class="form-group mb-3">
                        <label class="form-label">Judul Rapat</label>
                        <select name="id_rapat" id="id_rapat" class="form-control" required>
                            <option value="">-- Pilih Rapat --</option>
                            <?php while ($r = $rapats->fetch_assoc()): ?>
                                <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['judul_rapat']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Cari & Pilih Anggota Rapat</label>
                            <select name="penerima_anggota[]" id="anggota" multiple="multiple" class="form-control"></select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Cari & Pilih Instansi/Dinas</label>
                            <select name="penerima_instansi[]" id="instansi" multiple="multiple" class="form-control"></select>
                        </div>
                    </div>

                    <!-- Include Bootstrap Multiselect -->
                    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
                    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/css/bootstrap.min.css">
                    <script src="https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/js/bootstrap.min.js"></script>

                    <link href="https://cdn.jsdelivr.net/npm/bootstrap-multiselect@0.9.15/dist/css/bootstrap-multiselect.css" rel="stylesheet" />
                    <script src="https://cdn.jsdelivr.net/npm/bootstrap-multiselect@0.9.15/dist/js/bootstrap-multiselect.min.js"></script>

                    <script>
                        $(document).ready(function() {
                            $('#anggota').html(`<?php
                                                $anggota->data_seek(0);
                                                while ($a = $anggota->fetch_assoc()) {
                                                    echo "<option value='{$a['id']}'>" . htmlspecialchars($a['nama']) . "</option>";
                                                }
                                                ?>`);
                            $('#instansi').html(`<?php
                                                    $instansi->data_seek(0);
                                                    while ($i = $instansi->fetch_assoc()) {
                                                        echo "<option value='{$i['id']}'>" . htmlspecialchars($i['nama']) . "</option>";
                                                    }
                                                    ?>`);
                            $('#anggota').multiselect({
                                includeSelectAllOption: true,
                                enableFiltering: true,
                                enableCaseInsensitiveFiltering: true,
                                maxHeight: 300,
                                buttonWidth: '100%',
                                nonSelectedText: 'Pilih Anggota'
                            });
                            $('#instansi').multiselect({
                                includeSelectAllOption: true,
                                enableFiltering: true,
                                enableCaseInsensitiveFiltering: true,
                                maxHeight: 300,
                                buttonWidth: '100%',
                                nonSelectedText: 'Pilih Instansi/Dinas'
                            });
                        });
                    </script>

                    <div class="form-group mb-3">
                        <label class="form-label">Tanggal Rapat</label>
                        <input type="date" id="tanggal" name="tanggal" class="form-control" required>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">Jam Rapat</label>
                        <input type="time" id="jam" name="jam" class="form-control" required>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">Lokasi</label>
                        <input type="text" id="lokasi" name="lokasi" class="form-control" placeholder="Masukkan lokasi rapat" required>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">Catatan Tambahan</label>
                        <textarea name="catatan" rows="3" class="form-control" placeholder="Opsional..."></textarea>
                    </div>

                    <div class="text-end">
                        <a href="undangan.php" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fe fe-save me-1"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php require_once LAYOUT_PATH . '/scripts.php'; ?>
    <script>
        // AJAX: Auto isi detail jadwal
        document.getElementById('id_rapat').addEventListener('change', function() {
            let id = this.value;
            if (id) {
                fetch(`add.php?ajax_jadwal=${id}`)
                    .then(res => res.json())
                    .then(data => {
                        document.getElementById('tanggal').value = data.tanggal || '';
                        document.getElementById('jam').value = data.waktu || '';
                        document.getElementById('lokasi').value = data.tempat || '';
                    })
                    .catch(err => console.error(err));
            } else {
                document.getElementById('tanggal').value = '';
                document.getElementById('jam').value = '';
                document.getElementById('lokasi').value = '';
            }
        });
    </script>
</body>

</html>