<?php
session_start();
include '../dashboard_header.php';
include '../../includes/inc_koneksi.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'developer', 'teknisi'])) {
    $_SESSION['error_message'] = "Anda tidak memiliki akses untuk melihat halaman manajemen materi.";
    header('Location: ../../login.php');
    exit();
}

$page_title = "Manajemen Materi Pembelajaran";
$success_message = '';
$error_message = '';

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
$search_query = isset($_GET['search']) ? $koneksi->real_escape_string($_GET['search']) : '';
$where_clause = '';
if (!empty($search_query)) {
    $where_clause = " WHERE m.title LIKE '%$search_query%' OR m.content LIKE '%$search_query%' OR c.name LIKE '%$search_query%' OR cl.name LIKE '%$search_query%' OR u.username LIKE '%$search_query%'";
}

// Fetch total number of materials for pagination
$sql_count = "SELECT COUNT(m.id) AS total
              FROM materials m
              LEFT JOIN categories c ON m.category_id = c.id
              LEFT JOIN classes cl ON m.class_id = cl.id
              LEFT JOIN users u ON m.uploaded_by = u.id"
              . $where_clause;
$result_count = $koneksi->query($sql_count);
$total_materials = 0;
if ($result_count) {
    $row_count = $result_count->fetch_assoc();
    $total_materials = $row_count['total'];
    $result_count->free();
} else {
    $error_message = "Gagal mengambil jumlah total materi: " . $koneksi->error;
}
$total_pages = ceil($total_materials / $limit);

// Fetch all materials for display
$sql = "SELECT m.id, m.title, SUBSTRING(m.content, 1, 150) AS content_excerpt, m.file_url, m.material_type,
               c.name AS category_name, cl.name AS class_name, u.username AS uploader_username, m.updated_at
        FROM materials m
        LEFT JOIN categories c ON m.category_id = c.id
        LEFT JOIN classes cl ON m.class_id = cl.id
        LEFT JOIN users u ON m.uploaded_by = u.id"
        . $where_clause . " ORDER BY m.updated_at DESC LIMIT $start, $limit";
$result = $koneksi->query($sql);
$materials = [];
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $materials[] = $row;
    }
    $result->free();
} else if ($result === false) {
    $error_message = "Gagal mengambil data materi: " . $koneksi->error;
}
?>

<div class="content-wrapper mb-5" style="min-width: 100%;">
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
                <?php echo $success_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                <?php echo $error_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Daftar Materi</h3>
                <div class="box-tools pull-right d-flex align-items-center">
                    
                    <a href="add.php" class="btn btn-primary btn-l mb-3 mt-3"><i class="fa fa-plus me-1"></i> Tambah Materi Baru</a>
                    <form action="" method="GET" class="search-form me-3 m-3 h-100">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Cari materi..." value="<?php echo htmlspecialchars($search_query); ?>">
                            <div class="input-group-btn">
                                <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i></button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="box-body" style="overflow-x: auto; ">
                <?php if (!empty($materials)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-primary">
                        <thead>
                            <tr>
                                <th style="width: 50px;">No.</th>
                                <th>Judul Materi</th>
                                <th>Kategori</th>
                                <th>Kelas</th>
                                <th>Diunggah Oleh</th>
                                <th style="width: 150px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = $start + 1; ?>
                            <?php foreach ($materials as $material): ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo htmlspecialchars($material['title']); ?></td>
                                    <td><?php echo htmlspecialchars($material['category_name'] ?: 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($material['class_name'] ?: 'N/A'); ?></td>
                                   
                                    <td><?php echo htmlspecialchars($material['uploader_username'] ?: 'N/A'); ?></td>
                                    
                                    <td>
                                        <a href="edit.php?id=<?php echo $material['id']; ?>" class="btn btn-warning btn-xs me-1" title="Edit Materi"><i class="fa fa-edit"></i></a>
                                        <a href="delete.php?id=<?php echo $material['id']; ?>" class="btn btn-danger btn-xs"
                                            onclick="return confirm('Apakah Anda yakin ingin menghapus materi <?php echo htmlspecialchars($material['title']); ?>? Ini tidak dapat dibatalkan!');"
                                            title="Hapus Materi">
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
                        Tidak ada materi yang ditemukan<?php echo !empty($search_query) ? " untuk pencarian " . htmlspecialchars($search_query) . "." : "."; ?>
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