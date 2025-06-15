<?php
session_start();
require_once '../../includes/inc_koneksi.php'; // sesuaikan path

// --- PENTING: Aktifkan script Summernote di footer untuk halaman ini ---
$enable_material_scripts = true; 
// --- AKHIR PENTING ---

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'developer', 'teknisi'])) {
    $_SESSION['error_message'] = "Akses ditolak.";
    header("Location: ../../login.php");
    exit();
}

$page_title = "Tambah Tugas";
$kelas_result = mysqli_query($koneksi, "SELECT id, name FROM classes ORDER BY name");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil dan bersihkan data dari form
    $title = $koneksi->real_escape_string(trim($_POST['title']));
    // Konten diambil dari Summernote
    $description = $koneksi->real_escape_string($_POST['description']); 
    $deadline = $koneksi->real_escape_string(trim($_POST['deadline'])); // Deadline juga perlu disanitasi
    $kelas_id = !empty($_POST['kelas_id']) ? (int)$_POST['kelas_id'] : NULL; // Pastikan int atau NULL
    $created_by = $_SESSION['user_id'];

    // Validasi dasar
    if (empty($title) || empty($description) || empty($deadline)) {
        $_SESSION['error_message'] = "Judul, Deskripsi, dan Deadline wajib diisi.";
    } else {
        // Query INSERT menggunakan prepared statement
        $sql = "INSERT INTO assignments (title, description, deadline, kelas_id, created_by) VALUES (?, ?, ?, ?, ?)";
        $stmt = $koneksi->prepare($sql);

        if ($stmt) {
            // Tentukan tipe parameter untuk bind_param
            // title (s), description (s), deadline (s), kelas_id (i/s), created_by (i)
            $bind_types = "";
            $bind_params = [];

            // Deskripsi: Karena bisa dari Summernote, bind sebagai string
            $bind_types .= "sss"; // title, description, deadline
            $bind_params[] = &$title;
            $bind_params[] = &$description;
            $bind_params[] = &$deadline;

            // kelas_id bisa NULL, bind dinamis
            if ($kelas_id === NULL) {
                $bind_types .= "s"; // Bind sebagai string jika NULL
                $bind_params[] = NULL;
            } else {
                $bind_types .= "i"; // Bind sebagai integer jika ada nilai
                $bind_params[] = &$kelas_id;
            }

            $bind_types .= "i"; // created_by
            $bind_params[] = &$created_by;

            // Panggil bind_param secara dinamis
            call_user_func_array([$stmt, 'bind_param'], array_merge([$bind_types], $bind_params));

            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Tugas '" . htmlspecialchars($title) . "' berhasil ditambahkan.";
                header("Location: index.php");
                exit();
            } else {
                $_SESSION['error_message'] = "Gagal menambahkan tugas: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $_SESSION['error_message'] = "Error menyiapkan statement: " . $koneksi->error;
        }
    }
    // Jika ada error_message, redirect ke halaman ini lagi agar alert tampil
    if (!empty($_SESSION['error_message'])) {
        header("Location: add.php"); // Redirect ke halaman tambah untuk tampilkan error
        exit();
    }
}
require_once '../dashboard_header.php'; // Panggil header dashboard
?>

<div class="content-wrapper mb-5" style="min-width: 100%;">
    <section class="content-header">
        <h1><?= $page_title ?></h1>
        <ol class="breadcrumb gap-2">
            <li><a href="../../index.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="index.php">Manajemen Tugas</a></li>
            <li class="active"><?= $page_title ?></li>
        </ol>
    </section>

    <div class="container mt-4">
        <?php if (isset($_SESSION['error_message'])): // Tampilkan error dari sesi ?>
            <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                <?= htmlspecialchars($_SESSION['error_message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['error_message']); // Hapus pesan setelah ditampilkan ?>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label for="title" class="form-label">Judul Tugas</label>
                <input type="text" class="form-control" name="title" id="title" value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>" required>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Deskripsi</label>
                <textarea name="description" id="material_content" class="form-control" rows="4"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
            </div>

            <div class="mb-3">
                <label for="kelas_id" class="form-label">Kelas</label>
                <select name="kelas_id" id="kelas_id" class="form-select">
                    <option value="">-- Pilih Kelas (Opsional) --</option>
                    <?php while ($kelas = mysqli_fetch_assoc($kelas_result)): ?>
                        <option value="<?= htmlspecialchars($kelas['id']) ?>" <?php echo (isset($_POST['kelas_id']) && $_POST['kelas_id'] == $kelas['id']) ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($kelas['name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="deadline" class="form-label">Deadline</label>
                <input type="datetime-local" class="form-control" name="deadline" id="deadline" value="<?php echo isset($_POST['deadline']) ? htmlspecialchars($_POST['deadline']) : ''; ?>" required>
            </div>

            <button type="submit" class="btn btn-success">Simpan</button>
            <a href="index.php" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
</div>

<?php 
// Tutup koneksi database
if (isset($koneksi) && is_object($koneksi) && $koneksi->ping()) {
    $koneksi->close();
}
require_once '../dashboard_footer.php'; 
?>