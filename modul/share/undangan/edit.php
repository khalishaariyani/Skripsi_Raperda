<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once ROOT_PATH . '/session_start.php';

// Proteksi admin
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.php?msg=unauthorized&obj=undangan");
    exit;
}

$pageTitle = 'Edit Undangan Rapat';
$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: undangan.php?msg=invalid&obj=undangan");
    exit;
}

// Ambil data lama
$stmt = $conn->prepare("SELECT * FROM undanganrapat WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();
if (!$data) {
    header("Location: undangan.php?msg=invalid&obj=undangan");
    exit;
}

// Ambil rapat, anggota, dan instansi
$rapats = $conn->query("SELECT id, judul_rapat FROM jadwalrapat WHERE status != 'dibatalkan' ORDER BY tanggal DESC");
$anggota = $conn->query("SELECT id, nama FROM user WHERE role = 'anggota' ORDER BY nama ASC");
$instansi = $conn->query("SELECT id, nama FROM anggotadinas WHERE isAktif = 1 ORDER BY nama ASC");

// Proses submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idRapat = intval($_POST['idRapat'] ?? 0);
    $tanggal = $_POST['tanggal'] ?? '';
    $jam     = $_POST['jam'] ?? '';
    $lokasi  = trim($_POST['lokasi'] ?? '');
    $catatan = trim($_POST['catatan'] ?? '');
    $penerimaAnggota = $_POST['penerima_anggota'] ?? [];
    $penerimaInstansi = $_POST['penerima_instansi'] ?? [];

    $penerima = '';

    if (is_array($penerimaAnggota) && count($penerimaAnggota) > 0) {
        $namaList = [];
        foreach ($penerimaAnggota as $idA) {
            $get = $conn->prepare("SELECT nama FROM user WHERE id = ? AND role = 'anggota'");
            $get->bind_param("i", $idA);
            $get->execute();
            $res = $get->get_result();
            if ($row = $res->fetch_assoc()) {
                $namaList[] = $row['nama'];
            }
        }
        $penerima = implode(', ', $namaList);
    } elseif (is_array($penerimaInstansi) && count($penerimaInstansi) > 0) {
        $namaList = [];
        foreach ($penerimaInstansi as $idI) {
            $get = $conn->prepare("SELECT nama FROM anggotadinas WHERE id = ?");
            $get->bind_param("i", $idI);
            $get->execute();
            $res = $get->get_result();
            if ($row = $res->fetch_assoc()) {
                $namaList[] = $row['nama'];
            }
        }
        $penerima = implode(', ', $namaList);
    }

    // Validasi field
    if (!$idRapat || !$tanggal || !$jam || !$lokasi || empty($penerima)) {
        header("Location: edit.php?id=$id&msg=kosong&obj=undangan");
        exit;
    }

    // Update data
    $stmt = $conn->prepare("UPDATE undanganrapat SET idRapat = ?, penerima = ?, tanggal = ?, jam = ?, lokasi = ?, catatan = ? WHERE id = ?");
    $stmt->bind_param("isssssi", $idRapat, $penerima, $tanggal, $jam, $lokasi, $catatan, $id);

    if ($stmt->execute()) {
        header("Location: undangan.php?msg=updated&obj=undangan");
        exit;
    } else {
        header("Location: edit.php?id=$id&msg=error&obj=undangan");
        exit;
    }
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

        <main class="main-content app-content mt-0">
            <div class="container-fluid mt-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Edit Undangan Rapat</h5>
                    </div>
                    <form method="POST" class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>

                        <div class="form-group mb-3">
                            <label class="form-label">Judul Rapat</label>
                            <select name="idRapat" class="form-control" required>
                                <option value="">-- Pilih Rapat --</option>
                                <?php while ($r = $rapats->fetch_assoc()): ?>
                                    <option value="<?= $r['id'] ?>" <?= ($r['id'] == $data['idRapat']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($r['judul_rapat']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Pilih Anggota</label>
                                <select name="penerima_anggota[]" id="anggota" multiple="multiple" class="form-control"></select>
                            </div>

                        </div>

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
                                                        $selected = (stripos($data['penerima'], $a['nama']) !== false) ? "selected" : "";
                                                        echo "<option value='{$a['id']}' $selected>" . htmlspecialchars($a['nama']) . "</option>";
                                                    }
                                                    ?>`);
                                $('#instansi').html(`<?php
                                                        $instansi->data_seek(0);
                                                        while ($i = $instansi->fetch_assoc()) {
                                                            $selected = (stripos($data['penerima'], $i['nama']) !== false) ? "selected" : "";
                                                            echo "<option value='{$i['id']}' $selected>" . htmlspecialchars($i['nama']) . "</option>";
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
                            <label class="form-label">Tanggal</label>
                            <input type="date" name="tanggal" class="form-control"
                                value="<?= htmlspecialchars($data['tanggal'] ?? '') ?>" required>
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label">Jam</label>
                            <input type="time" name="jam" class="form-control"
                                value="<?= htmlspecialchars($data['jam'] ?? '') ?>" required>
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label">Lokasi</label>
                            <input type="text" name="lokasi" class="form-control"
                                value="<?= htmlspecialchars($data['lokasi'] ?? '') ?>" required>
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label">Catatan Tambahan</label>
                            <textarea name="catatan" class="form-control" rows="3"><?= htmlspecialchars($data['catatan'] ?? '') ?></textarea>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="undangan.php" class="btn btn-secondary">Kembali</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fe fe-save me-1"></i> Simpan Perubahan
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