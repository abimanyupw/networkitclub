<?php
session_start();
include '../dashboard_header.php';
include '../../includes/inc_koneksi.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'developer', 'teknisi'])) {
    $_SESSION['error_message'] = "Anda tidak memiliki akses untuk melihat halaman manajemen materi.";
    header('Location: ../../login.php');
    exit();
}

$page_title = "Manajemen Badges User";
$search_query = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : '';
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 10;
$offset = ($current_page - 1) * $limit;

// Hitung total data
$count_query = "
SELECT COUNT(*) AS total
FROM user_badges ub
JOIN users u ON ub.user_id = u.id
JOIN badges b ON ub.badge_id = b.id
WHERE u.full_name LIKE '%$search_query%' OR b.name LIKE '%$search_query%'
";
$count_result = mysqli_query($koneksi, $count_query);
$total_data = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_data / $limit);

// Ambil data sesuai halaman
$query = "
SELECT ub.id, u.full_name AS siswa, b.name AS badge, ub.given_at, ub.note, pemberi.full_name AS pemberi
FROM user_badges ub
JOIN users u ON ub.user_id = u.id
JOIN badges b ON ub.badge_id = b.id
LEFT JOIN users pemberi ON ub.given_by = pemberi.id
WHERE u.full_name LIKE '%$search_query%' OR b.name LIKE '%$search_query%'
ORDER BY ub.given_at DESC
LIMIT $limit OFFSET $offset
";
$result = mysqli_query($koneksi, $query);
?>

<div class="content-wrapper mb-5" style="min-width: 100%;">
    <section class="content-header">
        <h1><?php echo $page_title; ?></h1>
        <ol class="breadcrumb gap-2">
            <li><a href="../../index.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li class="active"><?php echo $page_title; ?></li>
        </ol>
    </section>

    <div class="container mt-4">
       <div class="box-header with-border">
            <h3 class="box-title m-0">Riwayat Badge Siswa</h3>
            <div class="box-tools pull-right d-flex align-items-center gap-2">
                <form action="" method="GET" class="search-form d-flex align-items-center">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Cari badge atau siswa..." value="<?= htmlspecialchars($search_query) ?>">
                        <div class="input-group-btn">
                            <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i></button>
                        </div>
                    </div>
                </form>

                <a href="add.php" class="btn btn-primary">
                    <i class="fa fa-plus me-1"></i> Tambah Badge Baru
                </a>
            </div>
        </div>

        <table class="table table-bordered table-striped mt-2">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Siswa</th>
                    <th>Badge</th>
                    <th>Diberikan Oleh</th>
                    <th>Tanggal</th>
                    <th>Catatan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($result) > 0): $no = $offset + 1; ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= htmlspecialchars($row['siswa']) ?></td>
                            <td><?= htmlspecialchars($row['badge']) ?></td>
                            <td><?= htmlspecialchars($row['pemberi'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($row['given_at']) ?></td>
                            <td><?= htmlspecialchars($row['note']) ?></td>
                            <td>
                                <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning"><i class="fa fa-edit"></i></a>
                                <a href="delete.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus pemberian badge ini?')"><i class="fa fa-trash"></i></a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="7" class="text-center">Belum ada badge yang diberikan</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center">
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
