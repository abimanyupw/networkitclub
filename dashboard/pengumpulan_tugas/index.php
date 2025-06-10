<?php
// dashboard/submissions/index.php
session_start();
require_once '../../includes/inc_koneksi.php';
require_once '../dashboard_header.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'developer', 'teknisi'])) {
    $_SESSION['error_message'] = "Anda tidak memiliki akses ke halaman ini.";
    header("Location: ../../login.php");
    exit();
}

$page_title = "Pengumpulan Tugas";
$search_query = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : '';
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 10;
$offset = ($current_page - 1) * $limit;

// Hitung total
$count_query = "
SELECT COUNT(*) as total
FROM assignment_submissions s
JOIN assignments a ON s.assignment_id = a.id
JOIN users u ON s.user_id = u.id
WHERE u.full_name LIKE '%$search_query%' OR a.title LIKE '%$search_query%'
";
$total_data = mysqli_fetch_assoc(mysqli_query($koneksi, $count_query))['total'];
$total_pages = ceil($total_data / $limit);

// Ambil data pengumpulan
$query = "
SELECT s.id, u.full_name AS siswa, a.title AS tugas, s.file_path, s.submitted_at, s.status
FROM assignment_submissions s
JOIN assignments a ON s.assignment_id = a.id
JOIN users u ON s.user_id = u.id
WHERE u.full_name LIKE '%$search_query%' OR a.title LIKE '%$search_query%'
ORDER BY s.submitted_at DESC
LIMIT $limit OFFSET $offset
";
$result = mysqli_query($koneksi, $query);
?>

<div class="content-wrapper mb-5" style="min-width: 100%;">
    <section class="content-header">
        <h1><?= $page_title ?></h1>
        <ol class="breadcrumb gap-2">
            <li><a href="../../index.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li class="active">Pengumpulan Tugas</li>
        </ol>
    </section>

    <div class="container mt-4">
        <div class="box-header with-border">
            <h3 class="box-title">Daftar Pengumpulan</h3>
            <div class="box-tools pull-right d-flex align-items-center">
                <form action="" method="GET" class="search-form me-3 m-3 h-100">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Cari tugas atau siswa..." value="<?= htmlspecialchars($search_query) ?>">
                        <div class="input-group-btn">
                            <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <table class="table table-bordered table-striped mt-2">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Siswa</th>
                    <th>Judul Tugas</th>
                    <th>File</th>
                    <th>Waktu Kirim</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($result) > 0): $no = $offset + 1; ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= htmlspecialchars($row['siswa']) ?></td>
                            <td><?= htmlspecialchars($row['tugas']) ?></td>
                            <td>
                                <?php if ($row['file_path']): ?>
                                    <a href="../../uploads/<?= htmlspecialchars($row['file_path']) ?>" target="_blank">Lihat File</a>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($row['submitted_at']) ?></td>
                            <td>
                                <?php
                                $status_badge = [
                                    'pending' => 'warning',
                                    'approved' => 'success',
                                    'rejected' => 'danger'
                                ];
                                ?>
                                <span class="badge bg-<?= $status_badge[$row['status']] ?>"><?= ucfirst($row['status']) ?></span>
                            </td>
                            <td>
                                <?php if ($row['status'] === 'pending'): ?>
                                    <a href="approve.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-success" onclick="return confirm('Setujui tugas ini?')">Setujui</a>
                                    <a href="reject.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tolak tugas ini?')">Tolak</a>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="7" class="text-center">Belum ada tugas yang dikumpulkan</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center mt-4">
                <?php if ($current_page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $current_page - 1 ?>&search=<?= urlencode($search_query) ?>">&laquo;</a>
                    </li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= $i == $current_page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search_query) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>

                <?php if ($current_page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $current_page + 1 ?>&search=<?= urlencode($search_query) ?>">&raquo;</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</div>

<?php require_once '../dashboard_footer.php'; ?>