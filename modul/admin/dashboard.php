<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once ROOT_PATH . '/session_start.php';
require_once CONFIG_PATH . '/koneksi.php';

$pageTitle = 'Dashboard Admin';
$role = $_SESSION['role'] ?? 'guest';
$nama = $_SESSION['nama'] ?? 'User';

// ✅ Query Agenda Rapat Terdekat
$jadwal = $conn->query("
    SELECT * FROM jadwalrapat 
    WHERE tanggal >= CURDATE()
    ORDER BY tanggal ASC 
    LIMIT 5
");


// Ambil bulan dan tahun sekarang

$bulan = date('m');
$tahun = date('Y');

$hadir = 0;
$izin = 0;
$sakit = 0;

$query = $conn->query("SELECT status, COUNT(*) as total FROM kehadiranrapat 
                       WHERE MONTH(waktu_hadir) = '$bulan' AND YEAR(waktu_hadir) = '$tahun' 
                       GROUP BY status");

while ($row = $query->fetch_assoc()) {
    if ($row['status'] == 'hadir') {
        $hadir = $row['total'];
    } elseif ($row['status'] == 'izin') {
        $izin = $row['total'];
    } elseif ($row['status'] == 'sakit') {
        $sakit = $row['total'];
    }
}

// ✅ Query Arsip Rapat
$arsip = $conn->query("SELECT * FROM arsiprapat ORDER BY tanggal_upload DESC LIMIT 5");

// Informasi Terkini dengan pagination
$limit = 3;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;
$totalInfo = $conn->query("SELECT COUNT(*) as total FROM informasi")->fetch_assoc()['total'] ?? 0;
$totalPages = ceil($totalInfo / $limit);
$informasi = $conn->query("SELECT * FROM informasi ORDER BY tanggal DESC LIMIT $offset, $limit");
?>


<!DOCTYPE html>
<html lang="id" dir="ltr" data-nav-layout="vertical" data-theme-mode="light" data-header-styles="light" data-menu-styles="dark" data-toggled="close">
<?php require_once LAYOUT_PATH . '/head.php'; ?>

<body class="d-flex flex-column min-vh-100">

    <div class="page d-flex flex-column flex-grow-1">
        <?php require_once LAYOUT_PATH . '/header.php'; ?>
        <?php require_once LAYOUT_PATH . '/sidebar.php'; ?>

        <main class="main-content app-content flex-grow-1 p-4">
            <div class="container-fluid">
                <div class="mb-4">
                    <h3 class="fw-bold">Halo, <?= strtolower($nama) ?> 👋</h3>
                    <p class="text-muted">Selamat datang di sistem <strong>Raperda</strong> — Anda login sebagai <strong><?= ucfirst($role) ?></strong>.</p>
                </div>

                <!-- ✅ SATU ROW BESAR -->
                <div class="row g-4">

                    <!-- Agenda Rapat -->
                    <div class="col-md-4">
                        <div class="card h-100 shadow border-0 rounded-4 bg-gradient" style="background: linear-gradient(135deg, #f8fafc 60%, #ffe0e7 100%);">
                            <div class="card-header bg-transparent fw-bold border-0 fs-5 text-primary">
                                <i class="bi bi-calendar-event me-2"></i>Agenda Rapat Terdekat
                            </div>
                            <div class="card-body">
                                <?php if ($jadwal->num_rows > 0): ?>
                                    <?php while ($r = $jadwal->fetch_assoc()): ?>
                                        <div class="mb-3 p-3 rounded-3" style="background: #fff5f8;">
                                            <div class="fw-semibold fs-6"><?= htmlspecialchars($r['judul_rapat']) ?></div>
                                            <div class="text-muted small mb-1">
                                                <i class="bi bi-calendar3 me-1"></i><?= date('d M Y', strtotime($r['tanggal'])) ?>
                                                <span class="mx-2">|</span>
                                                <i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($r['tempat'] ?? '-') ?>
                                            </div>
                                            <?php
                                            $badge = [
                                                'usulan' => 'warning',
                                                'disetujui' => 'success',
                                                'dibatalkan' => 'danger'
                                            ][$r['status']] ?? 'secondary';
                                            ?>
                                            <span class="badge bg-<?= $badge ?> text-uppercase mt-1 px-3 py-1"><?= htmlspecialchars($r['status']) ?></span>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <p class="text-muted">Belum ada rapat terdekat.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Status Kehadiran -->
                    <div class="col-md-4">
                        <div class="card h-100 shadow border-0 rounded-4 bg-gradient"
                            style="background: linear-gradient(135deg, #f8fafc 60%, #ffb3d1 100%);">
                            <div class="card-header bg-transparent fw-bold border-0 fs-5 text-primary">
                                <i class="bi bi-people-fill me-2"></i>Status Kehadiran Peserta (<?= date('F Y') ?>)
                            </div>
                            <div class="card-body d-flex flex-column gap-3">
                                <!-- Hadir -->
                                <div class="d-flex align-items-center justify-content-between px-2 py-2 rounded-3" style="background: #e7fbe7;">
                                    <span class="d-flex align-items-center">
                                        <span class="badge bg-success rounded-pill me-2" style="width: 1.75em; height: 1.75em; display: flex; align-items: center; justify-content: center;">
                                            <i class="bi bi-check-lg" style="font-size: 1.1em;"></i>
                                        </span>
                                        <span class="fw-semibold">Hadir</span>
                                    </span>
                                    <span class="fs-5 fw-bold text-success"><?= $hadir ?></span>
                                </div>
                                <!-- Izin -->
                                <div class="d-flex align-items-center justify-content-between px-2 py-2 rounded-3" style="background: #fffbe7;">
                                    <span class="d-flex align-items-center">
                                        <span class="badge bg-warning rounded-pill me-2" style="width: 1.75em; height: 1.75em; display: flex; align-items: center; justify-content: center;">
                                            <i class="bi bi-exclamation-lg" style="font-size: 1.1em;"></i>
                                        </span>
                                        <span class="fw-semibold">Izin</span>
                                    </span>
                                    <span class="fs-5 fw-bold text-warning"><?= $izin ?></span>
                                </div>
                                <!-- Sakit -->
                                <div class="d-flex align-items-center justify-content-between px-2 py-2 rounded-3" style="background: #ffe7e7;">
                                    <span class="d-flex align-items-center">
                                        <span class="badge bg-danger rounded-pill me-2" style="width: 1.75em; height: 1.75em; display: flex; align-items: center; justify-content: center;">
                                            <i class="bi bi-emoji-frown" style="font-size: 1.1em;"></i>
                                        </span>
                                        <span class="fw-semibold">Sakit</span>
                                    </span>
                                    <span class="fs-5 fw-bold text-danger"><?= $sakit ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Finalisasi & Berita Acara -->
                    <div class="col-md-4">
                        <div class="card h-100 shadow border-0 rounded-4 bg-gradient" style="background: linear-gradient(135deg, #f8fafc 60%, #ffb347 100%);">
                            <div class="card-header bg-transparent fw-bold border-0 fs-5 text-info">
                                <i class="bi bi-journal-text me-2"></i>Finalisasi & Berita Acara
                            </div>
                            <div class="card-body">
                                <?php if ($arsip->num_rows > 0): ?>
                                    <?php while ($r = $arsip->fetch_assoc()): ?>
                                        <?php
                                        // Query to get the meeting title based on the related id_rapat
                                        $jadwalRapatQuery = $conn->query("SELECT judul_rapat FROM jadwalrapat WHERE id = {$r['id_rapat']}");
                                        $jadwalRapat = $jadwalRapatQuery->fetch_assoc();
                                        $judulRapat = $jadwalRapat['judul_rapat'] ?? 'Judul Tidak Tersedia';
                                        ?>
                                        <div class="mb-3 p-3 rounded-3" style="background: #fff7e6;">
                                            <div class="fw-semibold fs-6"><?= htmlspecialchars($judulRapat) ?></div>
                                            <div class="text-muted small mb-1">
                                                <i class="bi bi-calendar-event me-1"></i><?= date('d M Y', strtotime($r['tanggal_upload'])) ?>
                                            </div>
                                            <a href="<?= BASE_URL ?>/uploads/arsip/<?= urlencode($r['file_path']) ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold shadow-sm" target="_blank">Lihat</a>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <p class="text-muted">Belum ada berita acara.</p>
                                <?php endif; ?>
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
                    <div class="col-12">
                        <div class="card shadow border-0 rounded-4 mt-4">
                            <div class="card-header bg-transparent fw-bold border-0 fs-5 text-success">
                                <i class="bi bi-calendar3 me-2"></i>Kalender Kegiatan Rapat
                            </div>
                            <div class="card-body p-0">
                                <!-- Kontainer Kalender -->
                                <div id="calendar" style="min-height: 500px;"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Tambahkan CSS FullCalendar Custom -->
                    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
                    <style>
                        /* Kalender Modern Style */
                        .fc-toolbar-title {
                            font-size: 1.25rem;
                            font-weight: 600;
                            color: #333;
                        }

                        .fc-button {
                            background: #22c55e;
                            border: none;
                            border-radius: 6px;
                            padding: 6px 12px;
                            font-weight: 500;
                            color: #fff;
                        }

                        .fc-button:hover {
                            background: #16a34a;
                        }

                        .fc-event {
                            font-size: 0.75rem;
                            border-radius: 6px;
                            padding: 2px 4px;
                        }

                        .fc-daygrid-event {
                            background-color: #2563eb;
                            border: none;
                            color: #fff;
                        }
                    </style>

                    <!-- FullCalendar Script -->
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
                                        'usulan' => '#facc15',    // kuning soft
                                        'disetujui' => '#22c55e', // hijau
                                        'dibatalkan' => '#ef4444', // merah
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
                                        html: `
                        <div style="
                        font-size:12px;
                        font-weight:600;
                        line-height:1.2;
                        margin-bottom:2px;
                        word-break: break-word;
                        ">${arg.event.title}</div>
                        <div style="
                        font-size:10px;
                        line-height:1.2;
                        word-break: break-word;
                        ">${arg.event.extendedProps.description}</div>
                    `
                                    };
                                },

                                dayMaxEventRows: 2,
                                displayEventTime: false,
                                eventDisplay: 'block',
                                fixedWeekCount: false
                            });
                            calendar.render();
                        });
                    </script>


                    <?php require_once LAYOUT_PATH . '/scripts.php'; ?>
</body>

</html>