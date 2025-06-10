<?php
// dashboard/assignments/index.php
session_start();
require_once '../../includes/inc_koneksi.php';
require_once '../dashboard_header.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'developer', 'teknisi'])) {
    $_SESSION['error_message'] = "Anda tidak memiliki akses ke halaman tugas.";
    header("Location: ../../login.php");
    exit();
}

$page_title = "Manajemen Tugas";
$search_query = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : '';
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 10;
$offset = ($current_page - 1) * $limit;

// Hitung total data
$count_query = "SELECT COUNT(*) AS total FROM assignments a LEFT JOIN users u ON a.created_by = u.id WHERE a.title LIKE '%$search_query%'";
$total_data = mysqli_fetch_assoc(mysqli_query($koneksi, $count_query))['total'];
$total_pages = ceil($total_data / $limit);

// Ambil data tugas
$query = "
SELECT a.id, a.title, a.deadline, a.created_at, u.full_name AS pembuat, c.name AS kelas
FROM assignments a
LEFT JOIN users u ON a.created_by = u.id
LEFT JOIN classes c ON a.kelas_id = c.id
WHERE a.title LIKE '%$search_query%'
ORDER BY a.created_at DESC
LIMIT $limit OFFSET $offset
";
$result = mysqli_query($koneksi, $query);
?>

<div class="content-wrapper mb-5" style="min-width: 100%;">
    <section class="content-header">
        <h1><?= $page_title ?></h1>
        <ol class="breadcrumb gap-2">
            <li><a href="../../index.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li class="active">Tugas</li>
        </ol>
    </section>

    <div class="container mt-4">
        <div class="box-header with-border">
            <h3 class="box-title">Daftar Tugas</h3>
            <div class="box-tools pull-right d-flex align-items-center">
                <a href="add.php" class="btn btn-primary btn-l mb-3 mt-3">
                    <i class="fa fa-plus me-1"></i> Tambah Tugas
                </a>
                <form action="" method="GET" class="search-form me-3 m-3 h-100">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Cari tugas..." value="<?= htmlspecialchars($search_query) ?>">
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
                    <th>Judul</th>
                    <th>Kelas</th>
                    <th>Deadline</th>
                    <th>Dibuat Oleh</th>
                    <th>Dibuat Pada</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($result) > 0): $no = $offset + 1; ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= htmlspecialchars($row['title']) ?></td>
                            <td><?= htmlspecialchars($row['kelas'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($row['deadline']) ?></td>
                            <td><?= htmlspecialchars($row['pembuat'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($row['created_at']) ?></td>
                            <td>
                                <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning"><i class="fa fa-edit"></i></a>
                                <a href="delete.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus tugas ini?')"><i class="fa fa-trash"></i></a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="7" class="text-center">Belum ada tugas</td></tr>
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
