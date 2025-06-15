<?php
// dashboard/siswa/index.php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'siswa') {
    header('Location: ../../login.php');
    exit();
}

include '../../includes/inc_koneksi.php';

$user_id = $_SESSION['user_id'];

function getUserCountByRole($koneksi, $role) {
    $stmt = $koneksi->prepare("SELECT COUNT(*) AS count FROM users WHERE role = ?");
    $stmt->bind_param("s", $role);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return $row['count'];
}

function getClassCount($koneksi) {
    $result = $koneksi->query("SELECT COUNT(*) AS count FROM classes");
    $row = $result->fetch_assoc();
    return $row['count'];
}

function getMaterialCount($koneksi) {
    $result = $koneksi->query("SELECT COUNT(*) AS count FROM materials");
    $row = $result->fetch_assoc();
    return $row['count'];
}

function getCategoriesCount($koneksi) {
    $result = $koneksi->query("SELECT COUNT(*) AS count FROM categories");
    $row = $result->fetch_assoc();
    return $row['count'];
}

$materi_count = getMaterialCount($koneksi);
$kategori_count = getCategoriesCount($koneksi);
$kelas_count = getClassCount($koneksi);

$page_title = 'Dashboard Siswa';
include '../dashboard_header.php';
?>

<div class="container-fluid py-4">
    <h6 class="mb-2">Selamat Datang, <?php echo htmlspecialchars($logged_in_username); ?>!</h6>
    <h1 class="mb-2">Dashboard Siswa</h1>
</div>

<div class="container">
    <div class="row g-4 mb-4 p-3">
        <!-- Kelas Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card h-100 text-white bg-primary shadow py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-uppercase mb-1">Kelas</div>
                            <div class="h5 mb-0 font-weight-bold"><?php echo $kelas_count; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-book fa-2x text-white card-icon"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-secondary-subtle border-light-subtle text-end d-flex justify-content-end align-items-center">
                    <a href="../class_preview/index.php" class="text-black text-decoration-none small">More info <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
        </div>

        <!-- Materi Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card h-100 text-white shadow py-2" style="background-color: #ff8c00;">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-uppercase mb-1">Total Materi Tersedia</div>
                            <div class="h5 mb-0 font-weight-bold"><?php echo $materi_count; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-lines fa-2x text-white card-icon"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-secondary-subtle border-light-subtle text-end d-flex justify-content-end align-items-center"></div>
            </div>
        </div>

        <!-- Badge Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <?php
            $badge_result = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM user_badges WHERE user_id = $user_id");
            $badge_count = mysqli_fetch_assoc($badge_result)['total'];
            ?>
            <div class="card h-100 bg-success text-white shadow py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-uppercase mb-1">Badge Saya</div>
                            <div class="h5 mb-0 font-weight-bold"><?php echo $badge_count; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-award fa-2x text-white card-icon"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light text-end d-flex justify-content-end align-items-center">
                    <a href="../badge_siswa/index.php" class="text-dark text-decoration-none small">Lihat Semua <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
        </div>

        <!-- Tugas Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <?php
            $tugas_result = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM assignments");
            $tugas_count = mysqli_fetch_assoc($tugas_result)['total'];
            ?>
            <div class="card h-100 bg-warning text-white shadow py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-uppercase mb-1">Total Tugas</div>
                            <div class="h5 mb-0 font-weight-bold"><?php echo $tugas_count; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-tasks fa-2x text-white card-icon"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light text-end d-flex justify-content-end align-items-center">
                    <a href="../tugas_siswa/index.php" class="text-dark text-decoration-none small">Lihat Tugas <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
        </div>
    </div>

    <!-- Informasi Penting -->
    <div class="row justify-content-center">
        <div class="col-lg-12 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">Informasi Penting</h6>
                </div>
                <div class="card-body bg-light">
                    <p>Selamat datang di dashboard siswa NIC Club. Di sini Anda dapat melihat kelas yang Anda ikuti, mengakses materi, dan memantau kemajuan belajar Anda.</p>
                    <p>Segera jelajahi menu di samping untuk mulai belajar!</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Informasi Terbaru -->
    <?php
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
                                    <h5><?= htmlspecialchars($info['title']) ?></h5>
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

     <?php
    $query_tugas = "SELECT a.id, a.title, a.deadline FROM assignments a ORDER BY created_at DESC LIMIT 5";
    $result_tugas = $koneksi->query($query_tugas);
    ?>
    <div class="row justify-content-center">
        <div class="col-lg-12 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-warning text-white">
                    <h6 class="m-0 font-weight-bold">Tugas Terbaru</h6>
                </div>
                <div class="card-body bg-white">
                    <?php if ($result_tugas && $result_tugas->num_rows > 0): ?>
                        <ul class="list-group">
                            <?php while($tugas = $result_tugas->fetch_assoc()): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?= htmlspecialchars($tugas['title']) ?></strong><br>
                                        <small class="text-muted">Deadline: <?= htmlspecialchars($tugas['deadline']) ?></small>
                                    </div>
                                    <a href="../tugas_siswa/index.php" class="btn btn-sm btn-primary">Lihat</a>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-center">Belum ada tugas terbaru.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include '../dashboard_footer.php';
$koneksi->close();
?>