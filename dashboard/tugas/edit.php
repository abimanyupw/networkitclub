<?php
session_start();
require_once '../../includes/inc_koneksi.php';

// --- PENTING: Aktifkan script Summernote di footer untuk halaman ini ---
$enable_material_scripts = true; 
// --- AKHIR PENTING ---

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'developer', 'teknisi'])) {
    $_SESSION['error_message'] = "Akses ditolak.";
    header("Location: ../../login.php");
    exit();
}

$page_title = "Edit Tugas";

// Ambil ID tugas dari GET, validasi
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_message'] = "ID tugas tidak valid.";
    header("Location: index.php");
    exit();
}

$id = (int) $_GET['id']; // ID tugas yang akan diedit

// Ambil data tugas yang akan diedit
$sql_fetch_data = "SELECT id, title, description, deadline, kelas_id FROM assignments WHERE id = ?";
$stmt_fetch_data = mysqli_prepare($koneksi, $sql_fetch_data);
if (!$stmt_fetch_data) {
    $_SESSION['error_message'] = "Error menyiapkan query fetch: " . mysqli_error($koneksi);
    header("Location: index.php");
    exit();
}
mysqli_stmt_bind_param($stmt_fetch_data, "i", $id);
mysqli_stmt_execute($stmt_fetch_data);
$result_fetch_data = mysqli_stmt_get_result($stmt_fetch_data);
$data = mysqli_fetch_assoc($result_fetch_data); // Data tugas yang ada

if (!$data) {
    $_SESSION['error_message'] = "Tugas tidak ditemukan.";
    header("Location: index.php");
    exit();
}
mysqli_stmt_close($stmt_fetch_data); // Tutup statement fetch

// Ambil daftar kelas untuk dropdown
$kelas_result = mysqli_query($koneksi, "SELECT id, name FROM classes ORDER BY name");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil dan bersihkan data dari form
    $title = $koneksi->real_escape_string(trim($_POST['title']));
    // Konten diambil dari Summernote
    $description = $koneksi->real_escape_string($_POST['description']); 
    $deadline = $koneksi->real_escape_string(trim($_POST['deadline']));
    $kelas_id = !empty($_POST['kelas_id']) ? (int)$_POST['kelas_id'] : NULL;

    // Validasi dasar
    if (empty($title) || empty($description) || empty($deadline)) {
        $_SESSION['error_message'] = "Judul, Deskripsi, dan Deadline wajib diisi.";
    } else {
        // Query UPDATE menggunakan prepared statement
        $sql_update = "UPDATE assignments SET title = ?, description = ?, deadline = ?, kelas_id = ? WHERE id = ?";
        $stmt_update = mysqli_prepare($koneksi, $sql_update);

        if ($stmt_update) {
            // Tentukan tipe parameter untuk bind_param
            // title (s), description (s), deadline (s), kelas_id (i/s), id (i)
            $bind_types = "";
            $bind_params = [];

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

            $bind_types .= "i"; // ID tugas untuk WHERE
            $bind_params[] = &$id;

            // Panggil bind_param secara dinamis
            call_user_func_array([$stmt_update, 'bind_param'], array_merge([$bind_types], $bind_params));

            if (mysqli_stmt_execute($stmt_update)) {
                $_SESSION['success_message'] = "Tugas '" . htmlspecialchars($title) . "' berhasil diperbarui.";
                header("Location: index.php");
                exit();
            } else {
                $_SESSION['error_message'] = "Gagal memperbarui tugas: " . mysqli_stmt_error($stmt_update);
            }
            mysqli_stmt_close($stmt_update);
        } else {
            $_SESSION['error_message'] = "Error menyiapkan statement update: " . mysqli_error($koneksi);
        }
    }
    // Jika ada error_message dari POST, redirect ke halaman ini lagi agar alert tampil
    if (!empty($_SESSION['error_message'])) {
        header("Location: edit.php?id=" . $id); // Redirect kembali ke halaman edit
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
            <li class="active">Edit</li>
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
        <?php if (isset($_SESSION['success_message'])): // Tampilkan success dari sesi ?>
            <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                <?= htmlspecialchars($_SESSION['success_message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['success_message']); // Hapus pesan setelah ditampilkan ?>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label for="title" class="form-label">Judul Tugas</label>
                <input type="text" class="form-control" name="title" id="title" value="<?= htmlspecialchars($data['title']) ?>" required>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Deskripsi</label>
                <textarea name="description" id="material_content" class="form-control" rows="4"><?= htmlspecialchars($data['description']) ?></textarea>
            </div>

            <div class="mb-3">
                <label for="kelas_id" class="form-label">Kelas</label>
                <select name="kelas_id" id="kelas_id" class="form-select">
                    <option value="">-- Pilih Kelas (Opsional) --</option>
                    <?php while ($kelas = mysqli_fetch_assoc($kelas_result)): ?>
                        <option value="<?= htmlspecialchars($kelas['id']) ?>" <?= ($kelas['id'] == $data['kelas_id']) ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($kelas['name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="deadline" class="form-label">Deadline</label>
                <input type="datetime-local" class="form-control" name="deadline" id="deadline" value="<?= date('Y-m-d\TH:i', strtotime($data['deadline'])) ?>" required>
            </div>

            <button type="submit" class="btn btn-success">Simpan Perubahan</button>
            <a href="index.php" class="btn btn-secondary">Batal</a>
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