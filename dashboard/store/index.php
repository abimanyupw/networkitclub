<?php
session_start();
require_once '../../includes/inc_koneksi.php';


if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'developer', 'teknisi'])) {
    header("Location: ../../login.php");
    exit();
}

$page_title = "Manajemen Store";
$search = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : '';
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$total_query = "SELECT COUNT(DISTINCT s.id) FROM store_items s WHERE s.name LIKE '%$search%'";
$total_result = mysqli_fetch_array(mysqli_query($koneksi, $total_query));
$total_items = $total_result[0];
$total_pages = ceil($total_items / $limit);

$query = "
SELECT 
    s.id,
    s.name,
    s.description,
    s.image_url,
    s.stock,
    GROUP_CONCAT(
        DISTINCT CONCAT(b.name, ' (', r.badge_count_required, 'x)')
        ORDER BY b.name ASC
        SEPARATOR ', '
    ) AS required_badges
FROM 
    store_items s
LEFT JOIN 
    store_item_badge_rules r ON s.id = r.store_item_id
LEFT JOIN 
    badges b ON r.badge_id = b.id
WHERE 
    s.name LIKE '%$search%'
GROUP BY 
    s.id, s.name, s.description, s.image_url, s.stock, s.created_at
ORDER BY 
    s.created_at DESC
LIMIT $limit OFFSET $offset
";

$result = mysqli_query($koneksi, $query);

if (!$result) {
    echo "Error: " . mysqli_error($koneksi);
    exit();
}
require_once '../dashboard_header.php';
?>

<div class="content-wrapper mb-5">
    <section class="content-header">
        <h1><?= $page_title ?></h1>
        <ol class="breadcrumb gap-2">
            <li><a href="../../index.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li class="active">Store</li>
        </ol>
    </section>

    <div class="container mt-4">
        <div class="box-header with-border">
            <h3 class="box-title">Daftar Barang</h3>
        </div>
        <div class="box-header with-border mb-3 d-flex justify-content-flex-start align-items-center gap-2">
            <a href="add.php" class="btn btn-primary"><i class="fa fa-plus"></i> Tambah Barang</a>
            <form method="GET" class="search-form d-flex">
                <input type="text" name="search" class="form-control me-2" placeholder="Cari barang..." value="<?= htmlspecialchars($search) ?>">
                <button class="btn btn-primary" type="submit"><i class="fa fa-search"></i></button>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-primary">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Barang</th>
                        <th>Deskripsi</th>
                        <th>Gambar</th>
                        <th>Badge Diperlukan</th>
                        <th>Stok</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0): $no = $offset + 1; ?>
                        <?php while ($item = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= htmlspecialchars($item['name']) ?></td>
                                <td><?= htmlspecialchars($item['description']) ?></td>
                                <td>
                                    <?php if (!empty($item['image_url'])): ?>
                                        <img src="../../uploads/<?= htmlspecialchars($item['image_url']) ?>" width="60" alt="Gambar">
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($item['required_badges'] ?: 'Tidak ada') ?>
                                </td>
                                <td><?= (int)$item['stock'] ?></td>
                                <td>
                                    <a href="edit.php?id=<?= $item['id'] ?>" class="btn btn-sm btn-warning"><i class="fa fa-edit"></i></a>
                                    <a href="delete.php?id=<?= $item['id'] ?>" onclick="return confirm('Yakin ingin menghapus barang ini?')" class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center">Tidak ada data barang.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <nav>
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"> <?= $i ?> </a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>
</div>

<?php require_once '../dashboard_footer.php'; ?>