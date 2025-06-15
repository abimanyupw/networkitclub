<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../login.php');
    exit();
}

include '../../includes/inc_koneksi.php';

function getUserCountByRole($koneksi, $role) {
    $stmt = $koneksi->prepare("SELECT COUNT(*) AS count FROM users WHERE role = ?");
    $stmt->bind_param("s", $role);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return $row['count'];
}

function getCount($koneksi, $table) {
    $result = $koneksi->query("SELECT COUNT(*) AS count FROM $table");
    $row = $result->fetch_assoc();
    return $row['count'];
}
$admin_count = getUserCountByRole($koneksi, 'admin');
$teknisi_count = getUserCountByRole($koneksi, 'teknisi');
$siswa_count = getUserCountByRole($koneksi, 'siswa');
$kelas_count = getCount($koneksi, 'classes');
$materi_count = getCount($koneksi, 'materials');
$kategori_count = getCount($koneksi, 'categories');

// Grafik status pengumpulan
$status_result = mysqli_query($koneksi, "SELECT status, COUNT(*) AS total FROM assignment_submissions GROUP BY status");
$statuses = ['approved' => 0, 'pending' => 0, 'rejected' => 0];
while ($row = mysqli_fetch_assoc($status_result)) {
    $statuses[$row['status']] = $row['total'];
}

// Grafik pengumpulan mingguan
$submission_weeks = $submission_counts = [];
$result = mysqli_query($koneksi, "SELECT DATE_FORMAT(submitted_at, '%Y-%u') AS week, COUNT(*) AS jumlah FROM assignment_submissions GROUP BY week ORDER BY week ASC");
while ($row = mysqli_fetch_assoc($result)) {
    $submission_weeks[] = $row['week'];
    $submission_counts[] = $row['jumlah'];
}

// Grafik badge mingguan
$badge_weeks = $badge_counts = [];
$result = mysqli_query($koneksi, "SELECT DATE_FORMAT(given_at, '%Y-%u') AS week, COUNT(*) AS jumlah FROM user_badges GROUP BY week ORDER BY week ASC");
while ($row = mysqli_fetch_assoc($result)) {
    $badge_weeks[] = $row['week'];
    $badge_counts[] = $row['jumlah'];
}

$page_title = 'Dashboard Administrator';
include '../dashboard_header.php';
?>

<div class="container-fluid py-4">
    <h6 class="mb-2">Selamat Datang, <?php echo htmlspecialchars($logged_in_username); ?>!</h6>
    <h1 class="mb-2">Dashboard Administrator</h1>
</div>

<div class="container">
    <div class="row g-4 mb-4">
        <?php
        $cards = [
            ['Admin', $admin_count, 'fa-user-gear', 'bg-info', '../users/index.php'],
            ['Teknisi', $teknisi_count, 'fa-screwdriver-wrench', 'bg-primary', '../users/index.php'],
            ['Siswa', $siswa_count, 'fa-user-graduate', 'bg-warning', '../users/index.php'],
            ['Kelas', $kelas_count, 'fa-chalkboard-teacher', 'bg-success', '../class/index.php'],
            ['Kategori Materi', $kategori_count, 'fa-tags', 'bg-warning', '../kategori/index.php'],
            ['Materi', $materi_count, 'fa-book-open', 'bg-danger', '../materi/index.php'],
        ];
        foreach ($cards as [$title, $count, $icon, $bg, $link]) {
            echo <<<HTML
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card h-100 text-white $bg shadow py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-uppercase mb-1">$title</div>
                                <div class="h5 mb-0 font-weight-bold">$count</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas $icon fa-2x text-white card-icon"></i>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-light text-end">
                        <a href="$link" class="text-dark text-decoration-none small">More info <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
            </div>
HTML;
        }
        ?>
    </div>

    <!-- informasi -->
         <div class="container justify-content-center">
        <div class="row justify-content-center">
            <div class="col-lg-12 mb-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 bg-primary text-white">
                        <h6 class="m-0 font-weight-bold">Informasi Penting</h6>
                    </div>
                    <div class="card-body bg-light">
                        <p>Selamat datang di dashboard administrator! Di sini, Anda dapat mengelola semua aspek dari sistem pembelajaran kami.</p>
                        <p>Anda dapat menambahkan, mengedit, dan menghapus pengguna, kelas, materi, serta mengelola kemajuan belajar siswa.</p>
                        <p>Segera jelajahi menu di samping untuk mulai belajar!</p>
                    </div>
                </div>
            </div>
        </div>

                    <!-- Tambahan Informasi Terbaru -->
                    <?php
                    // Ambil data informasi terbaru 5 saja (bisa diubah)
                    $query_info = "SELECT * FROM informasi ORDER BY created_at DESC LIMIT 5";
                    $result_info = $koneksi->query($query_info);
                    ?>

                    <div class="row justify-content-center">
                        <div class="col-lg-12 mb-4">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 bg-info text-white">
                                    <h6 class="m-0 font-weight-bold">Informasi Terbaru</h6>
                                </div>
                                <div class="card-body bg-white">
                                    <?php if ($result_info && $result_info->num_rows > 0): ?>
                                        <ul class="list-group">
                                            <?php while($info = $result_info->fetch_assoc()): ?>
                                                <li class="list-group-item">
                                                    <h5><?= ($info['title']) ?></h5>
                                                    <p><?= nl2br(($info['content'])) ?></p>
                                                    <small class="text-muted">Diposting pada <?= $info['created_at'] ?></small>
                                                </li>
                                            <?php endwhile; ?>
                                        </ul>
                                    <?php else: ?>
                                        <p class="text-center">Belum ada informasi terbaru.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

    </div>


    <!-- Grafik -->
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card shadow">
                <div class="card-header bg-secondary text-white">Distribusi Role Pengguna</div>
                <div class="card-body">
                    <canvas id="userRoleChart" style="max-height: 340px; width:auto;"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-4">
            <div class="card shadow">
                <div class="card-header bg-warning text-white">Status Pengumpulan Tugas</div>
                <div class="card-body">
                    <canvas id="statusBarChart" height="180px"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-4">
            <div class="card shadow">
                <div class="card-header bg-info text-white">Tren Pengumpulan Tugas Mingguan</div>
                <div class="card-body">
                    <canvas id="submissionTrendChart" height="180px"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-4">
            <div class="card shadow">
                <div class="card-header bg-success text-white">Pemberian Badge Mingguan</div>
                <div class="card-body">
                    <canvas id="badgeTrendChart" height="180px"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
new Chart(document.getElementById('userRoleChart'), {
    type: 'pie',
    data: {
        labels: ['Admin','Teknisi', 'Siswa'],
        datasets: [{
            data: [<?= $admin_count?>,<?= $teknisi_count ?>, <?= $siswa_count ?>],
            backgroundColor: ['#6f42c1','#007bff', '#ffc107']
        }]
    },
    options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
});

new Chart(document.getElementById('statusBarChart'), {
    type: 'bar',
    data: {
        labels: ['Disetujui', 'Menunggu', 'Ditolak'],
        datasets: [{
            label: 'Jumlah',
            data: [<?= $statuses['approved'] ?>, <?= $statuses['pending'] ?>, <?= $statuses['rejected'] ?>],
            backgroundColor: ['#28a745', '#ffc107', '#dc3545']
        }]
    },
    options: { responsive: true, plugins: { legend: { display: false } } }
});

new Chart(document.getElementById('submissionTrendChart'), {
    type: 'line',
    data: {
        labels: <?= json_encode($submission_weeks) ?>,
        datasets: [{
            label: 'Pengumpulan per Minggu',
            data: <?= json_encode($submission_counts) ?>,
            borderColor: '#007bff',
            backgroundColor: 'rgba(0, 123, 255, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: { responsive: true }
});

new Chart(document.getElementById('badgeTrendChart'), {
    type: 'line',
    data: {
        labels: <?= json_encode($badge_weeks) ?>,
        datasets: [{
            label: 'Badge Diberikan per Minggu',
            data: <?= json_encode($badge_counts) ?>,
            borderColor: '#28a745',
            backgroundColor: 'rgba(40, 167, 69, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: { responsive: true }
});
</script>

<?php include '../dashboard_footer.php'; $koneksi->close(); ?>
