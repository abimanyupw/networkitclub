<?php
session_start();
include '../dashboard_header.php';
include '../../includes/inc_koneksi.php';

// Pastikan hanya user dengan role tertentu yang bisa mengakses
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'developer'])) {
    $_SESSION['error_message'] = "Anda tidak memiliki akses untuk melihat halaman manajemen kelas.";
    header('Location: ../../login.php');
    exit();
}

$page_title = "Manajemen Kelas Pembelajaran";
$success_message = '';
$error_message = '';

// Ambil pesan dari session jika ada
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}
if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}

// Pagination setup
$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Search functionality
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$classes = [];
$total_classes = 0;

if (!empty($search_query)) {
    $search_term = "%$search_query%";
    $sql_count = "SELECT COUNT(c.id) AS total FROM classes c LEFT JOIN users u ON c.created_by = u.id WHERE c.name LIKE ? OR c.description LIKE ? OR u.username LIKE ?";
    $stmt = $koneksi->prepare($sql_count);
    $stmt->bind_param("sss", $search_term, $search_term, $search_term);
    $stmt->execute();
    $result = $stmt->get_result();
    $total_classes = $result->fetch_assoc()['total'];
    $stmt->close();

    $sql = "SELECT c.id, c.name, c.description, SUBSTRING(c.description, 1, 150) AS description_excerpt, c.image_url,
                   u.username AS creator_username, c.created_at
            FROM classes c
            LEFT JOIN users u ON c.created_by = u.id
            WHERE c.name LIKE ? OR c.description LIKE ? OR u.username LIKE ?
            ORDER BY c.created_at DESC LIMIT ?, ?";
    $stmt = $koneksi->prepare($sql);
    $stmt->bind_param("sssii", $search_term, $search_term, $search_term, $start, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $classes[] = $row;
    }
    $stmt->close();
} else {
    $sql_count = "SELECT COUNT(id) AS total FROM classes";
    $result = $koneksi->query($sql_count);
    $total_classes = $result->fetch_assoc()['total'];

    $sql = "SELECT c.id, c.name, c.description, SUBSTRING(c.description, 1, 150) AS description_excerpt, c.image_url,
                   u.username AS creator_username, c.created_at
            FROM classes c
            LEFT JOIN users u ON c.created_by = u.id
            ORDER BY c.created_at DESC LIMIT ?, ?";
    $stmt = $koneksi->prepare($sql);
    $stmt->bind_param("ii", $start, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $classes[] = $row;
    }
    $stmt->close();
}

$total_pages = ceil($total_classes / $limit);
?>

<div class="content-wrapper mb-5">
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
                <h3 class="box-title">Daftar Kelas</h3>
                <div class="box-tools pull-right d-flex align-items-center">
                    <a href="add.php" class="btn btn-success mb-3"><i class="fa fa-plus-circle me-1"></i> Tambah Kelas</a>
                    <form action="" method="GET" class="search-form me-3 m-3 h-100">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Cari kelas..." value="<?php echo htmlspecialchars($search_query); ?>">
                            <div class="input-group-btn">
                                <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i></button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="box-body">
                <?php if (!empty($classes)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-primary">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Nama Kelas</th>
                                <th>Deskripsi</th>
                                <th>Gambar</th>
                                <th>Dibuat Oleh</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = $start + 1; ?>
                            <?php foreach ($classes as $class): ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo htmlspecialchars($class['name']); ?></td>
                                    <td><?php echo htmlspecialchars(strip_tags($class['description_excerpt'])); ?>...</td>
                                    <td>
                                        <?php if (!empty($class['image_url'])): ?>
                                            <img src="<?php echo htmlspecialchars($class['image_url']); ?>" alt="Gambar Kelas" style="max-height: 50px;">
                                        <?php else: ?>
                                            N/A
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($class['creator_username'] ?: 'N/A'); ?></td>
                                    
                                    <td>
                                        <a href="edit.php?id=<?php echo $class['id']; ?>" class="btn btn-warning btn-xs me-1" title="Edit Kelas"><i class="fa fa-edit"></i></a>
                                        <a href="delete.php?id=<?php echo $class['id']; ?>" class="btn btn-danger btn-xs" onclick="return confirm('Apakah Anda yakin ingin menghapus kelas <?php echo htmlspecialchars($class['name']); ?>?');" title="Hapus Kelas">
                                            <i class="fa fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <nav aria-label="Page navigation" class="mt-3">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item"><a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search_query); ?>">Previous</a></li>
                        <?php endif; ?>
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search_query); ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        <?php if ($page < $total_pages): ?>
                            <li class="page-item"><a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search_query); ?>">Next</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <?php else: ?>
                    <div class="alert alert-info" role="alert">
                        Tidak ada kelas yang ditemukan<?php echo !empty($search_query) ? " untuk pencarian '<b>" . htmlspecialchars($search_query) . "</b>'." : "."; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
</div>

<?php
$koneksi->close();
include '../dashboard_footer.php';
?>