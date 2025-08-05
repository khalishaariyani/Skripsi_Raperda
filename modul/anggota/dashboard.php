<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once ROOT_PATH . '/session_start.php';
require_once CONFIG_PATH . '/koneksi.php';

$pageTitle = 'Dashboard Anggota DPRD';
$role = $_SESSION['role'] ?? 'guest';
$nama  = $_SESSION['nama'] ?? 'User';
$id_user = intval($_SESSION['id'] ?? 0); // ✅ pastikan nama session konsisten

// 🔒 Proteksi hanya untuk Anggota
if ($role !== 'anggota') {
    header('Location: /raperda/index.php?msg=unauthorized');
    exit;
}

// ✅ Agenda Rapat Terdekat
$jadwal = $conn->query("
    SELECT * FROM jadwalrapat 
    WHERE status = 'disetujui' AND tanggal >= CURDATE()
    ORDER BY tanggal ASC LIMIT 5
");

// ✅ Jumlah Notulen
$total_notulen = $conn->query("SELECT COUNT(*) as total FROM notulen")->fetch_assoc()['total'] ?? 0;

// ✅ Dokumentasi Kegiatan (Pagination)
$perPage = 3;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $perPage;
$totalRows = $conn->query("SELECT COUNT(*) AS total FROM dokumentasikegiatan")->fetch_assoc()['total'] ?? 0;
$totalPages = ceil($totalRows / $perPage);

$dokList = $conn->query("
    SELECT dk.*, jr.judul_rapat
    FROM dokumentasikegiatan dk
    JOIN jadwalrapat jr ON dk.idRapat = jr.id
    ORDER BY dk.created_at DESC
    LIMIT $perPage OFFSET $offset
") or die($conn->error);

// ✅ Statistik Berita Acara (finalisasi) berdasarkan jumlah arsip rapat
$jumlah_ba = 0;
$tableCheckArsip = $conn->query("SHOW TABLES LIKE 'arsiprapat'");
if ($tableCheckArsip && $tableCheckArsip->num_rows > 0) {
    $count = $conn->query("SELECT COUNT(*) as total FROM arsiprapat");
    if ($count) {
        $jumlah_ba = $count->fetch_assoc()['total'] ?? 0;
    }
}


// ✅ Rekap Kehadiran Anggota (Hanya rapat yg disetujui)
$statuses = ['hadir', 'izin', 'sakit'];
$rekap = [];

foreach ($statuses as $status) {
    $stmt = $conn->prepare("
        SELECT COUNT(*) AS total 
        FROM kehadiranrapat kr
        JOIN jadwalrapat jr ON kr.id_rapat = jr.id
        WHERE kr.id_user = ? AND kr.status = ? AND jr.status = 'disetujui'
    ");
    $stmt->bind_param("is", $id_user, $status);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $rekap[$status] = $result['total'] ?? 0;
    $stmt->close();
}

$hadir = $rekap['hadir'];
$izin  = $rekap['izin'];
$sakit = $rekap['sakit'];

// ✅ Informasi Terkini (Pagination)
$limit = 3;
$offset = ($page - 1) * $limit;
$totalInfo = $conn->query("SELECT COUNT(*) as total FROM informasi")->fetch_assoc()['total'] ?? 0;
$totalPages = ceil($totalInfo / $limit);

$informasi = $conn->query("
    SELECT * FROM informasi 
    ORDER BY tanggal DESC 
    LIMIT $offset, $limit
");

?>


<!DOCTYPE html>
<html lang="id">
<?php require_once LAYOUT_PATH . '/head.php'; ?>

<body class="d-flex flex-column min-vh-100">
    <div class="page d-flex flex-column flex-grow-1">
        <?php require_once LAYOUT_PATH . '/header.php'; ?>
        <?php require_once LAYOUT_PATH . '/sidebar.php'; ?>

        <main class="main-content app-content flex-grow-1 p-4">
            <div class="container-fluid">

                <!-- Greeting -->
                <div class="mb-4">
                    <h3 class="fw-bold">Halo, <?= htmlspecialchars($nama) ?> 👋</h3>
                    <p class="text-muted">Selamat datang di <strong>Sistem Raperda</strong> — Anda login sebagai <strong>Anggota DPRD</strong>.</p>
                </div>

                <div class="row g-4 mb-4">
                    <!-- Rekap Kehadiran (3 kolom) -->
                    <div class="col-md-8">
                        <div class="card shadow rounded-4 border-0">
                            <div class="card-body">
                                <h5 class="fw-bold mb-4">Rekap Kehadiran Anda</h5>
                                <div class="row text-center">
                                    <div class="col">
                                        <div class="text-success fs-5 fw-bold"><?= $hadir ?></div>
                                        <div class="text-muted small">Hadir</div>
                                    </div>
                                    <div class="col">
                                        <div class="text-warning fs-5 fw-bold"><?= $izin ?></div>
                                        <div class="text-muted small">Izin</div>
                                    </div>
                                    <div class="col">
                                        <div class="text-danger fs-5 fw-bold"><?= $sakit ?></div>
                                        <div class="text-muted small">Sakit</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Finalisasi Berita Acara -->
                    <div class="col-md-4">
                        <div class="card shadow rounded-4 border-0 text-center">
                            <div class="card-body">
                                <h5 class="fw-bold mb-3">Finalisasi Berita Acara</h5>
                                <div class="fs-1 fw-bold text-primary"><?= $jumlah_ba ?></div>
                                <p class="text-muted mb-0">Jumlah BA Final disahkan.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ✅ Agenda & Notulen -->
                <div class="row g-4">
                    <!-- Agenda -->
                    <div class="col-md-6">
                        <div class="card bg-white shadow rounded-4">
                            <div class="card-header bg-transparent fw-bold border-0 fs-5 text-primary">
                                <i class="bi bi-calendar-event me-2"></i>Agenda Terdekat
                            </div>
                            <div class="card-body">
                                <?php if ($jadwal->num_rows > 0): ?>
                                    <?php while ($r = $jadwal->fetch_assoc()): ?>
                                        <div class="mb-3 p-3 border rounded-3 bg-white">
                                            <div class="fw-semibold"><?= htmlspecialchars($r['judul_rapat']) ?></div>
                                            <div class="text-muted small mb-1">
                                                <i class="bi bi-calendar3 me-1"></i><?= date('d M Y', strtotime($r['tanggal'])) ?>
                                                <span class="mx-2">|</span>
                                                <i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($r['tempat'] ?? '-') ?>
                                            </div>
                                            <span class="badge bg-success">Disetujui</span>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <p class="text-muted">Belum ada agenda terdekat.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Notulen -->
                    <div class="col-md-6">
                        <div class="card bg-white shadow rounded-4">
                            <div class="card-header bg-transparent fw-bold border-0 fs-5 text-primary">
                                <i class="bi bi-journal-text me-2"></i>Notulen Rapat
                            </div>
                            <div class="card-body d-flex flex-column" style="max-height: 400px;">
                                <div class="flex-grow-1 overflow-auto">
                                    <div class="fs-5 fw-bold mb-3"><?= $total_notulen ?> Notulen</div>
                                    <?php
                                    $sql = "
                    SELECT n.id, n.ringkasan, n.tanggal_input, j.judul_rapat
                    FROM notulen n
                    JOIN jadwalrapat j ON n.id_rapat = j.id
                    ORDER BY n.tanggal_input DESC
                    LIMIT 5
                ";
                                    $notulenList = $conn->query($sql);
                                    if ($notulenList && $notulenList->num_rows > 0) :
                                        while ($n = $notulenList->fetch_assoc()):
                                            $judul = !empty($n['ringkasan']) ? $n['ringkasan'] : 'Judul Notulen';
                                            $tanggal = !empty($n['tanggal_input']) ? date('d M Y', strtotime($n['tanggal_input'])) : '-';
                                    ?>
                                            <div class="mb-3 p-3 border rounded-3 bg-white">
                                                <div class="fw-bold text-primary"><?= htmlspecialchars($n['judul_rapat']) ?></div>
                                                <div class="fw-semibold"><?= htmlspecialchars($judul) ?></div>
                                                <div class="text-muted small mb-1">
                                                    <i class="bi bi-calendar3 me-1"></i><?= $tanggal ?>
                                                </div>
                                                <span class="badge bg-primary">Notulen</span>
                                            </div>
                                        <?php endwhile;
                                    else: ?>
                                        <p class="text-muted">Belum ada notulen.</p>
                                    <?php endif; ?>
                                </div>
                                <div class="text-center mt-3">
                                    <a href="<?= BASE_URL ?>/modul/share/notulen/notulen.php" class="btn btn-outline-primary rounded-pill px-4 fw-semibold">
                                        Lihat Semua Notulen
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- ✅ Dokumentasi -->
                <div class="card bg-white shadow rounded-4 mt-5">
                    <div class="card-header bg-transparent fw-bold border-0 fs-5 text-primary">
                        <i class="bi bi-camera-fill me-2"></i> Dokumentasi Kegiatan
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            <?php while ($d = $dokList->fetch_assoc()): ?>
                                <div class="col-md-4">
                                    <div class="p-3 border rounded shadow-sm h-100">
                                        <?php
                                        $files = !empty($d['file']) ? explode('|', $d['file']) : [];
                                        $fileUrl = BASE_URL . '/uploads/dokumentasi/' . urlencode($files[0] ?? '');
                                        ?>
                                        <?php if (!empty($files[0]) && preg_match('/\.(jpg|jpeg|png)$/i', $files[0])): ?>
                                            <img src="<?= $fileUrl ?>" alt="Foto" class="mb-2 w-100 rounded" style="height:120px;object-fit:cover;">
                                        <?php else: ?>
                                            <div style="width:100%;height:120px;background:#eee;border-radius:6px;"></div>
                                        <?php endif; ?>
                                        <div class="fw-semibold"><?= htmlspecialchars($d['judul_rapat']) ?></div>
                                        <span class="badge bg-success">Dokumentasi</span>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                            <!-- Navigasi -->
                            <div class="d-flex justify-content-between mt-4">
                                <?php if ($page > 1): ?>
                                    <a href="?page=<?= $page - 1 ?>" class="btn btn-primary">&laquo; Newer Posts</a>
                                <?php else: ?>
                                    <button class="btn btn-light" disabled>&laquo; Newer Posts</button>
                                <?php endif; ?>
                                <?php if ($page < $totalPages): ?>
                                    <a href="?page=<?= $page + 1 ?>" class="btn btn-outline-secondary">Older Posts &raquo;</a>
                                <?php else: ?>
                                    <button class="btn btn-light" disabled>Older Posts &raquo;</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informasi Terkini -->
            <div class="card shadow border-0 rounded-4 mt-4">
                <div class="card-header bg-transparent fw-bold fs-5 text-info">
                    <i class="bi bi-megaphone-fill me-2"></i>Informasi Terkini
                </div>
                <div class="card-body">
                    <?php if ($informasi && $informasi->num_rows > 0): ?>
                        <div class="row g-4">
                            <?php while ($info = $informasi->fetch_assoc()): ?>
                                <div class="col-md-4">
                                    <div class="p-4 border rounded shadow-sm h-100 hover-card">
                                        <span class="badge bg-primary mb-2"><?= date('d M Y', strtotime($info['tanggal'])) ?></span>

                                        <!-- Judul langsung link -->
                                        <h5 class="fw-bold">
                                            <a href="<?= BASE_URL ?>/modul/share/info/detail.php?id=<?= $info['id'] ?>" class="text-decoration-none">
                                                <?= htmlspecialchars($info['judul']) ?>
                                            </a>
                                        </h5>

                                        <p class="text-muted mb-0"><?= substr(strip_tags($info['isi']), 0, 120) ?>...</p>
                                    </div>
                                </div>


                            <?php endwhile; ?>
                        </div>
                        <div class="d-flex justify-content-between mt-4">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?= $page - 1 ?>" class="btn btn-primary">&laquo; Newer Posts</a>
                            <?php else: ?>
                                <button class="btn btn-light" disabled>&laquo; Newer Posts</button>
                            <?php endif; ?>
                            <?php if ($page < $totalPages): ?>
                                <a href="?page=<?= $page + 1 ?>" class="btn btn-outline-secondary">Older Posts &raquo;</a>
                            <?php else: ?>
                                <button class="btn btn-light" disabled>Older Posts &raquo;</button>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted mb-0">Belum ada informasi terkini.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Kalender Kegiatan -->
            <div class="card shadow border-0 rounded-4 mt-5">
                <div class="card-header bg-transparent fw-bold border-0 fs-5 text-success">
                    <i class="bi bi-calendar3 me-2"></i>Kalender Kegiatan Rapat
                </div>
                <div class="card-body p-0">
                    <div id="calendar" style="min-height: 500px;"></div>
                </div>
            </div>
    </div>
    </main>

    <?php require_once LAYOUT_PATH . '/footer.php'; ?>
    </div>


    <!-- FullCalendar -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'id',
                height: 500,
                headerToolbar: {
                    left: 'prev,next',
                    center: 'title',
                    right: ''
                },
                events: [
                    <?php
                    $jadwalKalender = $conn->query("SELECT * FROM jadwalrapat ORDER BY tanggal ASC");
                    $colors = [
                        'usulan' => '#facc15',
                        'disetujui' => '#22c55e',
                        'dibatalkan' => '#ef4444',
                    ];
                    while ($r = $jadwalKalender->fetch_assoc()):
                        $color = $colors[$r['status']] ?? '#38bdf8';
                    ?> {
                            title: "<?= addslashes($r['judul_rapat']) ?>",
                            start: "<?= $r['tanggal'] ?>",
                            description: "<?= addslashes($r['tempat'] ?? '-') ?>",
                            backgroundColor: "<?= $color ?>",
                            borderColor: "<?= $color ?>",
                            textColor: "#fff"
                        },
                    <?php endwhile; ?>
                ],
                eventContent: function(arg) {
                    return {
                        html: `<div style="font-size:12px;font-weight:600;">${arg.event.title}</div>
                       <div style="font-size:10px;">${arg.event.extendedProps.description}</div>`
                    };
                },
                displayEventTime: false
            });
            calendar.render();
        });
    </script>

    <?php require_once LAYOUT_PATH . '/scripts.php'; ?>
</body>

</html>