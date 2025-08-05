<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once ROOT_PATH . '/session_start.php';
require_once CONFIG_PATH . '/koneksi.php';

$pageTitle = 'Dashboard Persidangan';
$role = $_SESSION['role'] ?? 'guest';
$nama  = $_SESSION['nama'] ?? 'User';

// Proteksi role
if ($role !== 'persidangan') {
    header('Location: /raperda/index.php?msg=unauthorized');
    exit;
}

// Query agenda rapat
$jadwal = $conn->query("
    SELECT * FROM jadwalrapat 
    WHERE status = 'disetujui' AND tanggal >= CURDATE()
    ORDER BY tanggal ASC LIMIT 5
");

// Statistik
$total_usulan = $conn->query("SELECT COUNT(*) as total FROM jadwalrapat WHERE status = 'usulan'")
    ->fetch_assoc()['total'] ?? 0;

$total_notulen = $conn->query("SELECT COUNT(*) as total FROM notulen")->fetch_assoc()['total'] ?? 0;

// ⚡ FIX: Pakai nama tabel SAMA untuk total & list dokumentasi
$jumlah_dokumentasi = 0;
$tableCheckDok = $conn->query("SHOW TABLES LIKE 'dokumentasikegiatan'");
if ($tableCheckDok->num_rows > 0) {
    $jumlah_dokumentasi = $conn->query("SELECT COUNT(*) as total FROM dokumentasikegiatan")
        ->fetch_assoc()['total'] ?? 0;
}

// ✅ Statistik Berita Acara (finalisasi) berdasarkan jumlah arsip rapat
$jumlah_ba = 0;
$tableCheckArsip = $conn->query("SHOW TABLES LIKE 'arsiprapat'");
if ($tableCheckArsip && $tableCheckArsip->num_rows > 0) {
    $count = $conn->query("SELECT COUNT(*) as total FROM arsiprapat");
    if ($count) {
        $jumlah_ba = $count->fetch_assoc()['total'] ?? 0;
    }
}

// Informasi Terkini dengan pagination
$limit = 3;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;
$totalInfo = $conn->query("SELECT COUNT(*) as total FROM informasi")->fetch_assoc()['total'] ?? 0;
$totalPages = ceil($totalInfo / $limit);
$informasi = $conn->query("SELECT * FROM informasi ORDER BY tanggal DESC LIMIT $offset, $limit");
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
                    <h3 class="fw-bold">Hai, <?= htmlspecialchars($nama) ?> 👋</h3>
                    <p class="text-muted">Selamat datang di <strong>Sistem Raperda</strong> — Anda login sebagai <strong>Tim Persidangan</strong>.</p>
                </div>

                <!-- Row Cards -->
                <div class="row g-4">
                    <!-- Agenda -->
                    <div class="col-md-3">
                        <div class="card h-100 shadow border-0 rounded-4 bg-white">
                            <div class="card-header fw-bold fs-5 text-primary">
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
                                    <p class="text-muted">Belum ada rapat terdekat.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Notulen -->
                    <div class="col-md-3">
                        <div class="card h-100 shadow border-0 rounded-4 bg-white">
                            <div class="card-header fw-bold fs-5 text-primary">
                                <i class="bi bi-journal-text me-2"></i>Notulen Rapat
                            </div>
                            <div class="card-body d-flex flex-column">
                                <div class="flex-grow-1 overflow-auto pe-2">
                                    <div class="fs-4 fw-bold mb-3"><?= $total_notulen ?> Notulen</div>
                                    <?php
                                    $sql = "
                    SELECT n.id, n.ringkasan, n.tanggal_input, j.judul_rapat
                    FROM notulen n
                    JOIN jadwalrapat j ON n.id_rapat = j.id
                    ORDER BY n.tanggal_input DESC
                    LIMIT 5
                ";
                                    $notulenList = $conn->query($sql);
                                    if ($notulenList && $notulenList->num_rows > 0):
                                        while ($n = $notulenList->fetch_assoc()):
                                            $judul = !empty($n['ringkasan']) ? $n['ringkasan'] : ($n['id_rapat'] ?? 'Judul Notulen');
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
                                        <p class="text-muted">Belum ada Notulen disusun.</p>
                                    <?php endif; ?>
                                </div>
                                <div class="text-center mt-2">
                                    <a href="<?= BASE_URL ?>/modul/share/notulen/notulen.php" class="btn btn-outline-primary rounded-pill px-4 fw-semibold shadow-sm">
                                        <i class="bi bi-eye me-1"></i> Lihat Semua Notulen
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Dokumentasi -->
                    <div class="col-md-3">
                        <div class="card h-100 shadow border-0 rounded-4 bg-white">
                            <div class="card-header fw-bold fs-5 text-primary">
                                <i class="bi bi-camera-fill me-2"></i>Dokumentasi Kegiatan
                            </div>
                            <div class="card-body d-flex flex-column">
                                <div class="fs-4 fw-bold mb-3"><?= $jumlah_dokumentasi ?> Dokumentasi</div>
                                <div class="flex-grow-1 overflow-auto pe-2">
                                    <?php
                                    $dokList = $conn->query("SELECT * FROM dokumentasikegiatan ORDER BY created_at DESC LIMIT 5");
                                    if ($dokList && $dokList->num_rows > 0):
                                        while ($d = $dokList->fetch_assoc()):
                                            $ket = !empty($d['keterangan']) ? $d['keterangan'] : 'Tanpa Keterangan';
                                            $created = !empty($d['created_at']) ? date('d M Y', strtotime($d['created_at'])) : '-';
                                    ?>
                                            <div class="mb-3 p-3 border rounded-3 bg-white">
                                                <div class="fw-semibold"><?= htmlspecialchars($ket) ?></div>
                                                <div class="text-muted small mb-1"><i class="bi bi-calendar3 me-1"></i><?= $created ?></div>
                                                <span class="badge bg-success">Dokumentasi</span>
                                            </div>
                                        <?php endwhile;
                                    else: ?>
                                        <p class="text-muted">Belum ada dokumentasi diunggah.</p>
                                    <?php endif; ?>
                                </div>
                                <div class="text-center mt-2">
                                    <a href="<?= BASE_URL ?>/modul/share/kegiatan/dokumentasi.php" class="btn btn-outline-success rounded-pill px-4 fw-semibold shadow-sm">
                                        <i class="bi bi-eye me-1"></i> Lihat Semua Dokumentasi
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Berita Acara -->
                    <div class="col-md-3">
                        <div class="card h-100 shadow border-0 rounded-4 bg-white">
                            <div class="card-header fw-bold fs-5 text-primary">
                                <i class="bi bi-file-earmark-text-fill me-2"></i> Finalisasi Berita Acara
                            </div>
                            <div class="card-body text-center">
                                <div class="fs-1 fw-bold"><?= $jumlah_ba ?></div>
                                <p class="text-muted">Berita Acara rapat yang sudah diinput</p>
                                <a href="<?= BASE_URL ?>/modul/share/arsip/arsip.php"
                                    class="btn btn-outline-danger rounded-pill px-4 fw-semibold shadow-sm">Lihat Detail</a>
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

                <!-- Kalender -->
                <div class="card shadow border-0 rounded-4 mt-4">
                    <div class="card-header fw-bold fs-5 text-success">
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
                        $color = $colors[$r['status']] ?? '#05b4ffff';
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