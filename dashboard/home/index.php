<?php
// dashboard/manage_home/index.php

session_start(); // Memulai sesi PHP

// Mengikutkan file koneksi database (MySQLi)
require_once '../../includes/inc_koneksi.php'; // Pastikan jalur ini benar



// Memeriksa otorisasi pengguna
if (!isset($_SESSION['user_id']) || (!in_array($_SESSION['role'], ['developer', 'admin']))) {
    header('Location: ../../login.php');
    exit();
}

$page_title = 'Kelola Halaman Home';

// --- BAGIAN PENGAMBILAN DATA DARI DATABASE (MySQLi) ---
$home_content = [
    'heading1' => '', // Sesuaikan dengan nama kolom tabel Anda
    'heading2' => '', // Sesuaikan dengan nama kolom tabel Anda
    'qr_code_path' => '' // Sesuaikan dengan nama kolom tabel Anda
];

$query_select = "SELECT heading1, heading2, qr_code_path FROM home_content WHERE id = 1";
$result_select = mysqli_query($koneksi, $query_select);

if ($result_select) {
    $data_hero = mysqli_fetch_assoc($result_select);
    if ($data_hero) {
        $home_content['heading1'] = $data_hero['heading1'];
        $home_content['heading2'] = $data_hero['heading2'];
        $home_content['qr_code_path'] = $data_hero['qr_code_path'];
    }
    mysqli_free_result($result_select);
} else {
    $message = 'Error saat mengambil data dari database: ' . mysqli_error($koneksi);
    $message_type = 'danger';
    error_log("Error fetching hero section data in dashboard: " . mysqli_error($koneksi));
}
// --- AKHIR PENGAMBILAN DATA ---

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form menggunakan NAMA INPUT yang sesuai dengan kolom DB
    $new_heading1 = trim($_POST['heading1'] ?? '');
    $new_heading2 = trim($_POST['heading2'] ?? '');

    // Penanganan upload gambar
    $new_qr_code_path = $home_content['qr_code_path']; // Default ke gambar yang sudah ada
    $upload_dir = '../../img/'; // Folder tempat menyimpan gambar QR (sesuaikan)

    // Cek apakah ada file baru yang diunggah
    if (isset($_FILES['qr_code_path']) && $_FILES['qr_code_path']['error'] == UPLOAD_ERR_OK) {
        $file_tmp_name = $_FILES['qr_code_path']['tmp_name'];
        $file_name = basename($_FILES['qr_code_path']['name']);
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_ext = ['png', 'jpg', 'jpeg', 'gif', 'svg']; // Ekstensi yang diizinkan
        $max_file_size = 2 * 1024 * 1024; // 2MB

        if (in_array($file_ext, $allowed_ext) && $_FILES['qr_code_path']['size'] <= $max_file_size) {
            // Hapus gambar lama jika ada dan berbeda dari yang baru diunggah
            if (!empty($home_content['qr_code_path']) && file_exists($upload_dir . $home_content['qr_code_path']) && $home_content['qr_code_path'] !== $new_qr_code_path) {
                unlink($upload_dir . $home_content['qr_code_path']);
            }
            // Buat nama file unik untuk gambar baru
            $new_file_name = uniqid('qr_') . '.' . $file_ext;
            if (move_uploaded_file($file_tmp_name, $upload_dir . $new_file_name)) {
                $new_qr_code_path = $new_file_name;
            } else {
                $message = 'Gagal mengunggah gambar QR.';
                $message_type = 'danger';
            }
        } else {
            $message = 'Format gambar tidak didukung atau ukuran file terlalu besar (maks 2MB).';
            $message_type = 'danger';
        }
    }

    if (empty($new_heading1) || empty($new_heading2)) {
        $message = 'Judul utama dan subjudul hero section harus diisi.';
        $message_type = 'danger';
    } elseif ($message_type !== 'danger') { // Hanya lanjutkan jika tidak ada kesalahan upload
        // --- BAGIAN MEMPERBARUI DATA DI DATABASE (MySQLi) ---
        // PENTING: Gunakan mysqli_real_escape_string untuk mencegah SQL Injection
        $new_heading1_escaped = mysqli_real_escape_string($koneksi, $new_heading1);
        $new_heading2_escaped = mysqli_real_escape_string($koneksi, $new_heading2);
        $new_qr_code_path_escaped = mysqli_real_escape_string($koneksi, $new_qr_code_path);

        $update_query = "UPDATE home_content SET heading1 = '$new_heading1_escaped', heading2 = '$new_heading2_escaped', qr_code_path = '$new_qr_code_path_escaped' WHERE id = 1";

        if (mysqli_query($koneksi, $update_query)) {
            $message = 'Konten Hero Section berhasil diperbarui!';
            $message_type = 'success';
            // Perbarui $home_content agar data di formulir langsung ter-update setelah disimpan
            $home_content['heading1'] = $new_heading1;
            $home_content['heading2'] = $new_heading2;
            $home_content['qr_code_path'] = $new_qr_code_path;
        } else {
            $message = 'Error saat memperbarui data di database: ' . mysqli_error($koneksi);
            $message_type = 'danger';
            error_log("Error updating hero section data in dashboard: " . mysqli_error($koneksi));
        }
        // --- AKHIR BAGIAN DATABASE NYATA ---
    }
}
// Mengikutkan header dashboard (pastikan ini ada dan berfungsi)
require_once '../dashboard_header.php';
?>

<div class="content-wrapper">
    <div class="container-fluid">
        <h1 class="h3 mb-4 text-gray-800"><?php echo $page_title; ?></h1>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="card shadow mb-4 bg-dark text-white">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Edit Hero Section</h6>
            </div>
            <div class="card-body">
                <form action="index.php" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="heading1" class="form-label">Judul Utama Hero Section (H1)</label>
                        <input type="text" class="form-control" id="heading1" name="heading1" value="<?php echo htmlspecialchars($home_content['heading1']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="heading2" class="form-label">Subjudul Hero Section (H2)</label>
                        <input type="text" class="form-control" id="heading2" name="heading2" value="<?php echo htmlspecialchars($home_content['heading2']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="qr_code_path" class="form-label">Gambar QR Code</label>
                        <?php if (!empty($home_content['qr_code_path'])): ?>
                            <div class="mb-2">
                                <img src="../../img/<?php echo htmlspecialchars($home_content['qr_code_path']); ?>" alt="Current QR Code" style="max-width: 150px; border: 1px solid #ddd; padding: 5px;">
                                <small class="text-muted d-block">Gambar saat ini: <?php echo htmlspecialchars($home_content['qr_code_path']); ?></small>
                            </div>
                        <?php endif; ?>
                        <input class="form-control" type="file" id="qr_code_path" name="qr_code_path" accept="image/png, image/jpeg, image/gif, image/svg+xml">
                        <small class="form-text text-muted">Unggah gambar baru untuk mengganti gambar QR code yang ada. (Max 2MB, format: PNG, JPG, GIF, SVG)</small>
                    </div>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </form>
            </div>
        </div>

    </div>
</div>

<?php
require_once '../dashboard_footer.php';
?>