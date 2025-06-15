<?php
session_start();
// Sertakan file header dashboard

// Sertakan file koneksi database
include '../../includes/inc_koneksi.php';

// Cek apakah user sudah login dan memiliki role admin/developer
if (!isset($_SESSION['user_id']) || (!($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'developer'))) {
    // Simpan pesan error di sesi sebelum redirect
    $_SESSION['error_message'] = "Anda tidak memiliki akses untuk melihat halaman manajemen pengguna.";
    header('Location: ../../login.php'); // Arahkan ke halaman login
    exit();
}

$page_title = "Manajemen Pengguna"; // Atur judul halaman secara spesifik

$success_message = '';
$error_message = '';

// Ambil pesan sukses/error dari session jika ada (setelah operasi CRUD lain)
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']); // Hapus pesan dari sesi setelah diambil
}
if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']); // Hapus pesan dari sesi setelah diambil
}

// --- Logika Pencarian dan Filter ---
$search_query = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '';
$filter_role = isset($_GET['role']) ? htmlspecialchars($_GET['role']) : '';

$sql_conditions = [];
$bind_params = [];
$bind_types = '';

// Kondisi dasar berdasarkan role pengguna yang login
if ($_SESSION['role'] === 'admin') {
    $sql_conditions[] = "role != 'developer'";
}
// Untuk developer, tidak ada batasan role yang dilihat, semua user akan terlihat

// Tambahkan kondisi pencarian
if (!empty($search_query)) {
    // Mencari di username, email, atau full_name
    $sql_conditions[] = "(username LIKE ? OR email LIKE ? OR full_name LIKE ?)";
    $bind_types .= 'sss'; // Tiga 's' untuk tiga parameter string
    $bind_params[] = "%" . $search_query . "%";
    $bind_params[] = "%" . $search_query . "%";
    $bind_params[] = "%" . $search_query . "%";
}

// Tambahkan kondisi filter role
if (!empty($filter_role) && $filter_role !== 'all') {
    $sql_conditions[] = "role = ?";
    $bind_types .= 's';
    $bind_params[] = $filter_role;
}

$where_clause = '';
if (!empty($sql_conditions)) {
    $where_clause = " WHERE " . implode(' AND ', $sql_conditions);
}

// --- Logika Pagination ---
$records_per_page = 10; // Jumlah record per halaman
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $records_per_page;

// 1. Query untuk mendapatkan TOTAL BARIS (dengan filter & search yang sama)
$count_sql = "SELECT COUNT(id) AS total_records FROM users" . $where_clause;
$count_stmt = $koneksi->prepare($count_sql);

if ($count_stmt === false) {
    $error_message = "Gagal menyiapkan statement count: " . $koneksi->error;
    $total_records = 0; // Set ke 0 jika gagal
} else {
    if (!empty($bind_params)) {
        $count_stmt->bind_param($bind_types, ...$bind_params);
    }
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $total_records = $count_result->fetch_assoc()['total_records'];
    $count_stmt->close();
}

$total_pages = ceil($total_records / $records_per_page);

// 2. Query untuk mendapatkan data pengguna untuk HALAMAN SAAT INI
// Sekarang menyertakan full_name dan klausa LIMIT untuk pagination
$sql = "SELECT id, username, email, full_name, role FROM users" . $where_clause . " ORDER BY username ASC LIMIT ?, ?";

// Persiapan query menggunakan prepared statement untuk pencarian/filter
$stmt = $koneksi->prepare($sql);

if ($stmt === false) {
    $error_message = "Gagal menyiapkan statement utama: " . $koneksi->error;
} else {
    // Gabungkan tipe binding yang ada dengan 'ii' untuk LIMIT dan OFFSET
    $final_bind_types = $bind_types . 'ii';
    // Gabungkan parameter binding yang ada dengan OFFSET dan LIMIT
    $final_bind_params = array_merge($bind_params, [$offset, $records_per_page]);

    // Menggunakan operator spread (...) untuk melewatkan array sebagai argumen terpisah
    $stmt->bind_param($final_bind_types, ...$final_bind_params);

    $stmt->execute();
    $result = $stmt->get_result();

    // Cek jika query gagal setelah eksekusi
    if (!$result) {
        $error_message = "Gagal mengambil data pengguna dari database: " . $stmt->error;
        // Mungkin log error ini ke file log alih-alih menampilkannya langsung di produksi
    }
}
include '../dashboard_header.php';
?>

<main class="content-wrapper">
    <section class="content-header">
        <h1>Manajemen Pengguna</h1>
        <ol class="breadcrumb gap-2">
            <li><a href="../../index.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li class="active">Manajemen Pengguna</li>
        </ol>
    </section>

    <section class="content">
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                <?php echo$success_message; ?>
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
                <h3 class="box-title">Daftar Pengguna</h3>
                <div class="box-tools pull-right">
                    <a href="add.php" class="btn btn-primary btn-lg mb-3 mt-3"><i class="fa fa-plus me-1"></i> Tambah Pengguna Baru</a>
                </div>
            </div>
            <div class="mb-4 justify-content-start d-flex">
                <a href="download_template.php" class="btn btn-success me-2 btn-l">
                    <i class="fa fa-download me-1"></i> Download Template Excel
                </a>
                <a href="export_users.php?<?php echo http_build_query(['search' => $search_query, 'role' => $filter_role]); ?>" class="btn btn-warning me-2 btn-l">
                    <i class="fa fa-download me-1"></i> Export data
                </a>
                <a href="import_users.php" class="btn btn-info btn-l">
                    <i class="fa fa-upload me-1"></i> Upload Excel
                </a>
            </div>
            <div class="box-body">
                <form method="GET" action="" class="mb-4">
                    <div class="row">
                        <div class="col-md-4 mb-3 h-100">
                            <label for="search">Cari Pengguna:</label>
                            <input type="text" class="form-control" id="search" name="search" placeholder="Cari username, email, atau nama lengkap..." value="<?php echo $search_query; ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="role_filter">Filter Berdasarkan Role:</label>
                            <select class="form-control" id="role_filter" name="role">
                                <option value="all" <?php echo ($filter_role == 'all') ? 'selected' : ''; ?>>Semua Role</option>
                                <option value="admin" <?php echo ($filter_role == 'admin') ? 'selected' : ''; ?>>Admin</option>
                                <option value="siswa" <?php echo ($filter_role == 'siswa') ? 'selected' : ''; ?>>Siswa</option>
                                <option value="teknisi" <?php echo ($filter_role == 'teknisi') ? 'selected' : ''; ?>>Teknisi</option>
                                <?php if ($_SESSION['role'] === 'developer'): // Hanya developer bisa melihat role developer di filter ?>
                                    <option value="developer" <?php echo ($filter_role == 'developer') ? 'selected' : ''; ?>>Developer</option>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end mb-3">
                            <button type="submit" class="btn btn-info btn-block"><i class="fa fa-search me-1"></i> Cari & Filter</button>
                        </div>
                    </div>
                </form>

                <?php if (isset($result) && $result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-primary">
                        <thead>
                            <tr>
                                <th style="width: 50px;">No.</th>
                                <th>Username</th>
                                <th>Nama Lengkap</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th style="width: 150px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = $offset + 1; // Inisialisasi nomor urut berdasarkan offset ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                                    <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td><?php echo htmlspecialchars($row['role']); ?></td>
                                    <td>
                                        <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-xs me-1" title="Edit Pengguna"><i class="fa fa-edit"></i></a>
                                        <?php
                                        // Mencegah admin/developer menghapus akun mereka sendiri atau akun developer lain (jika admin)
                                        // dan mencegah admin menghapus developer
                                        $can_delete = true;
                                        if ($row['id'] == $_SESSION['user_id']) {
                                            $can_delete = false; // Tidak bisa menghapus diri sendiri
                                        }
                                        if ($_SESSION['role'] === 'admin' && $row['role'] === 'developer') {
                                            $can_delete = false; // Admin tidak bisa menghapus developer
                                        }
                                        ?>
                                        <?php if ($can_delete): ?>
                                            <a href="delete.php?id=<?php echo $row['id']; ?>" class="btn btn-danger btn-xs"
                                                onclick="return confirm('Apakah Anda yakin ingin menghapus pengguna <?php echo htmlspecialchars($row['username']); ?>? Ini tidak dapat dibatalkan!');"
                                                title="Hapus Pengguna">
                                                <i class="fa fa-trash"></i>
                                            </a>
                                        <?php else: ?>
                                            <button class="btn btn-danger btn-xs" disabled title="Tidak dapat menghapus pengguna ini"><i class="fa fa-trash"></i></button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <nav>
                    <ul class="pagination justify-content-center">
                        <?php if ($current_page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $current_page - 1; ?>&search=<?php echo $search_query; ?>&role=<?php echo $filter_role; ?>" aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo ($i == $current_page) ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo $search_query; ?>&role=<?php echo $filter_role; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($current_page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $current_page + 1; ?>&search=<?php echo $search_query; ?>&role=<?php echo $filter_role; ?>" aria-label="Next">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <?php else: ?>
                    <div class="alert alert-info" role="alert">
                        Tidak ada data pengguna yang tersedia dengan kriteria pencarian/filter tersebut.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
</main>

<?php
if (isset($stmt)) {
    $stmt->close(); // Tutup statement jika sudah dibuka
}
$koneksi->close();
// Sertakan file footer dashboard
include '../dashboard_footer.php';
?>