<?php
session_start();
include '../dashboard_header.php';
include '../../includes/inc_koneksi.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'developer', 'teknisi'])) {
    $_SESSION['error_message'] = "Anda tidak memiliki akses.";
    header('Location: ../../login.php');
    exit();
}

$page_title = "Manajemen Badges";
$success_message = $_SESSION['success_message'] ?? '';
$error_message = $_SESSION['error_message'] ?? '';
unset($_SESSION['success_message'], $_SESSION['error_message']);

$search_query = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : '';
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 10;
$offset = ($current_page - 1) * $limit;

// Hitung total badge
$count_query = "SELECT COUNT(*) as total FROM badges";
if ($search_query !== '') {
    $count_query .= " WHERE name LIKE '%$search_query%'";
}
$count_result = mysqli_query($koneksi, $count_query);
$total_data = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_data / $limit);

// Query badge
$sql = "SELECT * FROM badges";
if ($search_query !== '') {
    $sql .= " WHERE name LIKE '%$search_query%'";
}
$sql .= " ORDER BY id DESC LIMIT $limit OFFSET $offset";
$result = mysqli_query($koneksi, $sql);
?>

<div class="content-wrapper mb-5" style="min-width: 100%;">
    <section class="content-header">
        <h1><?php echo $page_title; ?></h1>
        <ol class="breadcrumb gap-2">
            <li><a href="../../index.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li class="active"><?php echo $page_title; ?></li>
        </ol>
    </section>

    <div class="container mt-2">
        <div class="box-header with-border">
            <h3 class="box-title">Daftar Badge</h3>
            <div class="box-tools pull-right d-flex align-items-center">
                <a href="add.php" class="btn btn-primary btn-l mb-3 mt-3">
                    <i class="fa fa-plus me-1"></i> Tambah Badge Baru
                </a>

                <form action="" method="GET" class="search-form me-3 m-3 h-100">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Cari badge..." value="<?= htmlspecialchars($search_query) ?>">
                        <div class="input-group-btn">
                            <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <?php if ($success_message): ?>
            <div class="alert alert-success alert-dismissible fade show mb-3"><?= htmlspecialchars($success_message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="alert alert-danger alert-dismissible fade show mb-3"><?= htmlspecialchars($error_message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>Deskripsi</th>
                    <th>Gambar</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($result) > 0): $no = $offset + 1; ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= htmlspecialchars($row['description']) ?></td>
                            <td>
                                <?php if (!empty($row['image_url'])): ?>
                                    <img src="../../uploads/<?= htmlspecialchars($row['image_url']) ?>" alt="Badge Image" width="50">
                                <?php else: ?>
                                    <span class="text-muted">(Tidak ada)</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning"><i class="fa fa-edit"></i></a>
                                <a href="delete.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus badge ini?')"><i class="fa fa-trash"></i></a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center">Belum ada badge</td>
                    </tr>
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
