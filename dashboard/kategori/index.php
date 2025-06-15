<?php
// dashboard/categories/index.php
session_start();
// Sertakan file header dashboard
// Sertakan file koneksi database
include '../../includes/inc_koneksi.php'; // Pastikan ini menyediakan objek $koneksi (mysqli)

// Cek apakah user sudah login dan memiliki role admin/developer/teknisi
if (!isset($_SESSION['user_id']) || (!($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'developer' || $_SESSION['role'] === 'teknisi'))) {
    $_SESSION['error_message'] = "Anda tidak memiliki akses untuk melihat halaman manajemen kategori.";
    header('Location: ../../login.php');
    exit();
}

$page_title = "Manajemen Kategori Materi"; // Atur judul halaman

$success_message = '';
$error_message = '';

// Ambil pesan sukses/error dari session jika ada (setelah operasi CRUD dari file lain)
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}
if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}

// Pagination setup
$limit = 10; // Number of entries per page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Search functionality
$search_query = isset($_GET['search']) ? $koneksi->real_escape_string($_GET['search']) : '';
$where_clause = '';
if (!empty($search_query)) {
    $where_clause = " WHERE name LIKE '%$search_query%' OR description LIKE '%$search_query%'";
}

// Fetch total number of categories for pagination
$sql_count = "SELECT COUNT(id) AS total FROM categories" . $where_clause;
$result_count = $koneksi->query($sql_count);
$total_categories = 0;
if ($result_count) {
    $row_count = $result_count->fetch_assoc();
    $total_categories = $row_count['total'];
    $result_count->free();
} else {
    $error_message = "Gagal mengambil jumlah total kategori: " . $koneksi->error;
}

$total_pages = ceil($total_categories / $limit);

// Fetch categories for display with pagination and search
$sql = "SELECT id, name, description FROM categories" . $where_clause . " ORDER BY name ASC LIMIT $start, $limit";
$result = $koneksi->query($sql);
$categories = [];
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
    $result->free(); // Bebaskan hasil
} else if ($result === false) {
    $error_message = "Gagal mengambil data kategori: " . $koneksi->error;
}
include '../dashboard_header.php';
?>

<div class="content-wrapper h-100">
    <section class="content-header">
        <h1><?php echo $page_title; ?></h1>
        <ol class="breadcrumb gap-2">
            <li><a href="../../index.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li class="active"><?php echo $page_title; ?></li>
        </ol>
    </section>

    <section class="content">
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                <?php echo htmlspecialchars($success_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                <?php echo htmlspecialchars($error_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Daftar Kategori</h3>
                <div class="box-tools pull-right d-flex align-items-center">
                    <a href="add.php" class="btn btn-primary btn-l mb-3 mt-3"><i class="fa fa-plus me-1"></i> Tambah Kategori Baru</a>
                    <form action="" method="GET" class="search-form me-3 m-3 h-100">
                        <div class="input-group">
                            <input type="search" name="search" class="form-control" placeholder="Cari kategori..." value="<?php echo htmlspecialchars($search_query); ?>">
                            <div class="input-group-btn">
                                <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i></button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="box-body">
                <?php if (!empty($categories)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-primary">
                        <thead>
                            <tr>
                                <th style="width: 50px;">No.</th>
                                <th>Nama Kategori</th>
                                <th>Deskripsi</th>
                                <th style="width: 150px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = $start + 1; // Adjust starting number for current page ?>
                            <?php foreach ($categories as $category): ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo htmlspecialchars($category['name']); ?></td>
                                    <td><?php echo htmlspecialchars($category['description']); ?></td>
                                    <td>
                                        <a href="edit.php?id=<?php echo $category['id']; ?>" class="btn btn-warning btn-xs me-1" title="Edit Kategori"><i class="fa fa-edit"></i></a>
                                        <a href="delete.php?id=<?php echo $category['id']; ?>" class="btn btn-danger btn-xs"
                                            onclick="return confirm('Apakah Anda yakin ingin menghapus kategori <?php echo htmlspecialchars($category['name']); ?>? Ini tidak dapat dibatalkan dan mungkin menghapus materi terkait!');"
                                            title="Hapus Kategori">
                                            <i class="fa fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <nav aria-label="Page navigation example" class="mt-3">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item"><a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo htmlspecialchars($search_query); ?>">Previous</a></li>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>"><a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo htmlspecialchars($search_query); ?>"><?php echo $i; ?></a></li>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                            <li class="page-item"><a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo htmlspecialchars($search_query); ?>">Next</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>

                <?php else: ?>
                    <div class="alert alert-info" role="alert">
                        Tidak ada kategori yang ditemukan<?php echo !empty($search_query) ? " untuk pencarian '<b>" . htmlspecialchars($search_query) . "</b>'." : "."; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
</div>

<?php
$koneksi->close(); // Tutup koneksi database
include '../dashboard_footer.php';
?>